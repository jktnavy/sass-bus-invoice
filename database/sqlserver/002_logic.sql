SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

USE bus_invoice_db;
GO

CREATE PROCEDURE dbo.sp_next_number
    @tenant_id UNIQUEIDENTIFIER,
    @doc_type NVARCHAR(20),
    @branch_id UNIQUEIDENTIFIER = NULL,
    @out_number NVARCHAR(80) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @prefix NVARCHAR(30), @suffix NVARCHAR(30), @padding INT, @next_value BIGINT;
    DECLARE @seq TABLE (
        prefix NVARCHAR(30),
        suffix NVARCHAR(30),
        padding INT,
        current_value BIGINT
    );

    BEGIN TRANSACTION;

    UPDATE dbo.number_sequences WITH (UPDLOCK, HOLDLOCK)
    SET current_value = current_value + 1,
        updated_at = SYSUTCDATETIME()
    OUTPUT INSERTED.prefix, INSERTED.suffix, INSERTED.padding, INSERTED.current_value
    INTO @seq(prefix, suffix, padding, current_value)
    WHERE tenant_id = @tenant_id
      AND doc_type = @doc_type
      AND ((branch_id IS NULL AND @branch_id IS NULL) OR branch_id = @branch_id);

    IF NOT EXISTS (SELECT 1 FROM @seq)
    BEGIN
        ROLLBACK TRANSACTION;
        THROW 50001, 'Number sequence not found.', 1;
    END

    SELECT TOP 1
        @prefix = prefix,
        @suffix = suffix,
        @padding = padding,
        @next_value = current_value
    FROM @seq;

    COMMIT TRANSACTION;

    SET @out_number = CONCAT(@prefix, RIGHT(REPLICATE('0', @padding) + CAST(@next_value AS NVARCHAR(30)), @padding), ISNULL(@suffix, ''));
END
GO

CREATE PROCEDURE dbo.sp_recalc_quotation_totals
    @quotation_id UNIQUEIDENTIFIER
AS
BEGIN
    SET NOCOUNT ON;

    ;WITH cte AS (
        SELECT
            qi.quotation_id,
            SUM(qi.qty * qi.price) AS sub_total,
            SUM(qi.discount) AS discount_total,
            SUM(CAST(((qi.qty * qi.price) - qi.discount) * ISNULL(t.rate, 0) / 100.0 AS DECIMAL(19,2))) AS tax_total
        FROM dbo.quotation_items qi
        LEFT JOIN dbo.taxes t ON t.id = qi.tax_id
        WHERE qi.quotation_id = @quotation_id
        GROUP BY qi.quotation_id
    )
    UPDATE q
    SET q.sub_total = ISNULL(c.sub_total, 0),
        q.discount_total = ISNULL(c.discount_total, 0),
        q.tax_total = ISNULL(c.tax_total, 0),
        q.grand_total = ISNULL(c.sub_total, 0) - ISNULL(c.discount_total, 0) + ISNULL(c.tax_total, 0),
        q.updated_at = SYSUTCDATETIME()
    FROM dbo.quotations q
    LEFT JOIN cte c ON c.quotation_id = q.id
    WHERE q.id = @quotation_id;
END
GO

CREATE PROCEDURE dbo.sp_recalc_invoice_totals
    @invoice_id UNIQUEIDENTIFIER
AS
BEGIN
    SET NOCOUNT ON;

    ;WITH cte AS (
        SELECT
            ii.invoice_id,
            SUM(ii.qty * ii.price) AS sub_total,
            SUM(ii.discount) AS discount_total,
            SUM(CAST(((ii.qty * ii.price) - ii.discount) * ISNULL(t.rate, 0) / 100.0 AS DECIMAL(19,2))) AS tax_total
        FROM dbo.invoice_items ii
        LEFT JOIN dbo.taxes t ON t.id = ii.tax_id
        WHERE ii.invoice_id = @invoice_id
        GROUP BY ii.invoice_id
    )
    UPDATE i
    SET i.sub_total = ISNULL(c.sub_total, 0),
        i.discount_total = ISNULL(c.discount_total, 0),
        i.tax_total = ISNULL(c.tax_total, 0),
        i.grand_total = ISNULL(c.sub_total, 0) - ISNULL(c.discount_total, 0) + ISNULL(c.tax_total, 0),
        i.updated_at = SYSUTCDATETIME()
    FROM dbo.invoices i
    LEFT JOIN cte c ON c.invoice_id = i.id
    WHERE i.id = @invoice_id;
END
GO

CREATE PROCEDURE dbo.sp_convert_quotation_to_invoice
    @quotation_id UNIQUEIDENTIFIER,
    @due_date DATE,
    @out_invoice_id UNIQUEIDENTIFIER OUTPUT,
    @out_invoice_number NVARCHAR(80) OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @tenant_id UNIQUEIDENTIFIER;
    DECLARE @customer_id UNIQUEIDENTIFIER;
    DECLARE @doc_date DATE;
    DECLARE @currency CHAR(3);
    DECLARE @notes NVARCHAR(MAX);

    SELECT
        @tenant_id = tenant_id,
        @customer_id = customer_id,
        @doc_date = [date],
        @currency = currency,
        @notes = notes
    FROM dbo.quotations
    WHERE id = @quotation_id;

    IF @tenant_id IS NULL
        THROW 50002, 'Quotation not found.', 1;

    BEGIN TRANSACTION;

    EXEC dbo.sp_next_number @tenant_id = @tenant_id, @doc_type = 'invoice', @branch_id = NULL, @out_number = @out_invoice_number OUTPUT;

    INSERT INTO dbo.invoices (
        tenant_id, number, [date], due_date, customer_id, status, currency, notes, source_quotation_id
    )
    VALUES (
        @tenant_id, @out_invoice_number, @doc_date, @due_date, @customer_id, 0, @currency, @notes, @quotation_id
    );

    SELECT @out_invoice_id = id FROM dbo.invoices WHERE number = @out_invoice_number AND tenant_id = @tenant_id;

    INSERT INTO dbo.invoice_items (
        tenant_id, invoice_id, item_id, name, description, qty, uom, price, discount, tax_id, sort_order
    )
    SELECT
        tenant_id,
        @out_invoice_id,
        item_id,
        name,
        description,
        qty,
        uom,
        price,
        discount,
        tax_id,
        sort_order
    FROM dbo.quotation_items
    WHERE quotation_id = @quotation_id;

    EXEC dbo.sp_recalc_invoice_totals @invoice_id = @out_invoice_id;

    UPDATE dbo.quotations
    SET status = 2,
        updated_at = SYSUTCDATETIME()
    WHERE id = @quotation_id;

    COMMIT TRANSACTION;
END
GO

CREATE PROCEDURE dbo.sp_post_payment
    @payment_id UNIQUEIDENTIFIER
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE dbo.payments
    SET status = 1,
        unapplied_amount = amount,
        updated_at = SYSUTCDATETIME()
    WHERE id = @payment_id
      AND status = 0;

    IF @@ROWCOUNT = 0
        THROW 50003, 'Payment must be in draft status to post.', 1;
END
GO

CREATE PROCEDURE dbo.sp_reverse_payment
    @payment_id UNIQUEIDENTIFIER
AS
BEGIN
    SET NOCOUNT ON;

    IF EXISTS (SELECT 1 FROM dbo.payment_allocations WHERE payment_id = @payment_id)
        THROW 50004, 'Cannot reverse payment with allocations.', 1;

    UPDATE dbo.payments
    SET status = 2,
        unapplied_amount = 0,
        updated_at = SYSUTCDATETIME()
    WHERE id = @payment_id
      AND status = 1;

    IF @@ROWCOUNT = 0
        THROW 50005, 'Payment must be in posted status to reverse.', 1;
END
GO

CREATE PROCEDURE dbo.sp_allocate_payment
    @payment_id UNIQUEIDENTIFIER,
    @invoice_id UNIQUEIDENTIFIER,
    @amount DECIMAL(19,2)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @tenant_id UNIQUEIDENTIFIER;
    DECLARE @payment_status TINYINT;
    DECLARE @unapplied DECIMAL(19,2);
    DECLARE @invoice_balance DECIMAL(19,2);

    IF @amount <= 0
        THROW 50006, 'Allocation amount must be positive.', 1;

    SELECT @tenant_id = tenant_id, @payment_status = status, @unapplied = unapplied_amount
    FROM dbo.payments
    WHERE id = @payment_id;

    IF @tenant_id IS NULL
        THROW 50007, 'Payment not found.', 1;

    IF @payment_status <> 1
        THROW 50008, 'Payment must be posted.', 1;

    SELECT @invoice_balance = balance_total
    FROM dbo.invoices
    WHERE id = @invoice_id
      AND tenant_id = @tenant_id;

    IF @invoice_balance IS NULL
        THROW 50009, 'Invoice not found or cross-tenant.', 1;

    IF @unapplied < @amount
        THROW 50010, 'Insufficient unapplied amount.', 1;

    IF @invoice_balance < @amount
        THROW 50011, 'Allocation exceeds invoice balance.', 1;

    INSERT INTO dbo.payment_allocations (tenant_id, payment_id, invoice_id, allocated_amount)
    VALUES (@tenant_id, @payment_id, @invoice_id, @amount);
END
GO

CREATE TRIGGER dbo.trg_quotation_items_recalc
ON dbo.quotation_items
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @ids TABLE (id UNIQUEIDENTIFIER PRIMARY KEY);

    INSERT INTO @ids(id)
    SELECT DISTINCT quotation_id FROM inserted WHERE quotation_id IS NOT NULL
    UNION
    SELECT DISTINCT quotation_id FROM deleted WHERE quotation_id IS NOT NULL;

    DECLARE @id UNIQUEIDENTIFIER;
    DECLARE cur CURSOR LOCAL FAST_FORWARD FOR SELECT id FROM @ids;

    OPEN cur;
    FETCH NEXT FROM cur INTO @id;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        EXEC dbo.sp_recalc_quotation_totals @quotation_id = @id;
        FETCH NEXT FROM cur INTO @id;
    END
    CLOSE cur;
    DEALLOCATE cur;
END
GO

CREATE TRIGGER dbo.trg_invoice_items_recalc
ON dbo.invoice_items
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @ids TABLE (id UNIQUEIDENTIFIER PRIMARY KEY);

    INSERT INTO @ids(id)
    SELECT DISTINCT invoice_id FROM inserted WHERE invoice_id IS NOT NULL
    UNION
    SELECT DISTINCT invoice_id FROM deleted WHERE invoice_id IS NOT NULL;

    DECLARE @id UNIQUEIDENTIFIER;
    DECLARE cur CURSOR LOCAL FAST_FORWARD FOR SELECT id FROM @ids;

    OPEN cur;
    FETCH NEXT FROM cur INTO @id;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        EXEC dbo.sp_recalc_invoice_totals @invoice_id = @id;
        FETCH NEXT FROM cur INTO @id;
    END
    CLOSE cur;
    DEALLOCATE cur;
END
GO

CREATE TRIGGER dbo.trg_payment_allocations_sync
ON dbo.payment_allocations
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @payments TABLE (id UNIQUEIDENTIFIER PRIMARY KEY);
    DECLARE @invoices TABLE (id UNIQUEIDENTIFIER PRIMARY KEY);

    INSERT INTO @payments(id)
    SELECT DISTINCT payment_id FROM inserted
    UNION
    SELECT DISTINCT payment_id FROM deleted;

    INSERT INTO @invoices(id)
    SELECT DISTINCT invoice_id FROM inserted
    UNION
    SELECT DISTINCT invoice_id FROM deleted;

    UPDATE p
    SET unapplied_amount = p.amount - ISNULL(a.total_allocated, 0),
        updated_at = SYSUTCDATETIME()
    FROM dbo.payments p
    LEFT JOIN (
        SELECT payment_id, SUM(allocated_amount) AS total_allocated
        FROM dbo.payment_allocations
        GROUP BY payment_id
    ) a ON a.payment_id = p.id
    WHERE p.id IN (SELECT id FROM @payments);

    UPDATE i
    SET paid_total = ISNULL(a.total_allocated, 0),
        status = CASE
            WHEN i.status = 4 THEN 4
            WHEN ISNULL(a.total_allocated, 0) <= 0 THEN CASE WHEN i.status = 1 THEN 1 ELSE 0 END
            WHEN ISNULL(a.total_allocated, 0) < i.grand_total THEN 2
            ELSE 3
        END,
        updated_at = SYSUTCDATETIME()
    FROM dbo.invoices i
    LEFT JOIN (
        SELECT invoice_id, SUM(allocated_amount) AS total_allocated
        FROM dbo.payment_allocations
        GROUP BY invoice_id
    ) a ON a.invoice_id = i.id
    WHERE i.id IN (SELECT id FROM @invoices);
END
GO

CREATE VIEW dbo.vw_ar_aging
AS
SELECT
    i.tenant_id,
    i.id AS invoice_id,
    i.number,
    i.customer_id,
    i.[date],
    i.due_date,
    i.grand_total,
    i.paid_total,
    i.balance_total,
    CASE
        WHEN i.balance_total <= 0 THEN 'settled'
        WHEN DATEDIFF(DAY, i.due_date, CAST(SYSUTCDATETIME() AS DATE)) <= 0 THEN 'current'
        WHEN DATEDIFF(DAY, i.due_date, CAST(SYSUTCDATETIME() AS DATE)) BETWEEN 1 AND 30 THEN '1-30'
        WHEN DATEDIFF(DAY, i.due_date, CAST(SYSUTCDATETIME() AS DATE)) BETWEEN 31 AND 60 THEN '31-60'
        WHEN DATEDIFF(DAY, i.due_date, CAST(SYSUTCDATETIME() AS DATE)) BETWEEN 61 AND 90 THEN '61-90'
        ELSE '>90'
    END AS aging_bucket,
    DATEDIFF(DAY, i.due_date, CAST(SYSUTCDATETIME() AS DATE)) AS overdue_days
FROM dbo.invoices i
WHERE i.status <> 4;
GO
