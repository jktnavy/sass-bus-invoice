SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

USE bus_invoice_db;
GO

IF SCHEMA_ID('sec') IS NULL EXEC ('CREATE SCHEMA sec');
GO

IF OBJECT_ID('dbo.tenants', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.tenants (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_tenants PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        code NVARCHAR(30) NOT NULL,
        name NVARCHAR(200) NOT NULL,
        npwp NVARCHAR(40) NULL,
        address NVARCHAR(500) NULL,
        email NVARCHAR(255) NULL,
        phone NVARCHAR(30) NULL,
        settings NVARCHAR(MAX) NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_tenants_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_tenants_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT UQ_tenants_code UNIQUE (code)
    );
END
GO

IF OBJECT_ID('dbo.users', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.users (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_users PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        name NVARCHAR(150) NOT NULL,
        email NVARCHAR(255) NOT NULL,
        password_hash NVARCHAR(255) NOT NULL,
        role NVARCHAR(20) NOT NULL,
        is_active BIT NOT NULL CONSTRAINT DF_users_is_active DEFAULT 1,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_users_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_users_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_users_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT CK_users_role CHECK (role IN ('superadmin','admin','sales','finance')),
        CONSTRAINT UQ_users_tenant_email UNIQUE (tenant_id, email)
    );
END
GO

IF OBJECT_ID('dbo.customers', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.customers (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_customers PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        code NVARCHAR(40) NOT NULL,
        name NVARCHAR(200) NOT NULL,
        npwp NVARCHAR(40) NULL,
        billing_address NVARCHAR(500) NULL,
        email NVARCHAR(255) NULL,
        payment_terms_days INT NOT NULL CONSTRAINT DF_customers_payment_terms DEFAULT 0,
        pic_name NVARCHAR(150) NULL,
        pic_phone NVARCHAR(30) NULL,
        notes NVARCHAR(MAX) NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_customers_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_customers_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_customers_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT UQ_customers_tenant_code UNIQUE (tenant_id, code)
    );
END
GO

IF OBJECT_ID('dbo.taxes', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.taxes (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_taxes PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        name NVARCHAR(100) NOT NULL,
        rate DECIMAL(9,4) NOT NULL,
        is_active BIT NOT NULL CONSTRAINT DF_taxes_active DEFAULT 1,
        applies_to NVARCHAR(20) NOT NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_taxes_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_taxes_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_taxes_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT CK_taxes_applies_to CHECK (applies_to IN ('item','document')),
        CONSTRAINT CK_taxes_rate CHECK (rate >= 0),
        CONSTRAINT UQ_taxes_tenant_name UNIQUE (tenant_id, name)
    );
END
GO

IF OBJECT_ID('dbo.items', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.items (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_items PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        code NVARCHAR(40) NOT NULL,
        name NVARCHAR(200) NOT NULL,
        type NVARCHAR(20) NOT NULL,
        uom NVARCHAR(20) NOT NULL,
        default_price DECIMAL(19,2) NOT NULL CONSTRAINT DF_items_default_price DEFAULT 0,
        tax_id UNIQUEIDENTIFIER NULL,
        metadata NVARCHAR(MAX) NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_items_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_items_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_items_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_items_tax FOREIGN KEY (tax_id) REFERENCES dbo.taxes(id),
        CONSTRAINT CK_items_type CHECK (type IN ('service','good')),
        CONSTRAINT UQ_items_tenant_code UNIQUE (tenant_id, code)
    );
END
GO

IF OBJECT_ID('dbo.number_sequences', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.number_sequences (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_number_sequences PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        doc_type NVARCHAR(20) NOT NULL,
        prefix NVARCHAR(30) NOT NULL,
        suffix NVARCHAR(30) NULL,
        padding INT NOT NULL CONSTRAINT DF_number_sequences_padding DEFAULT 6,
        current_value BIGINT NOT NULL CONSTRAINT DF_number_sequences_current DEFAULT 0,
        reset_policy NVARCHAR(20) NOT NULL CONSTRAINT DF_number_sequences_reset_policy DEFAULT 'none',
        branch_id UNIQUEIDENTIFIER NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_number_sequences_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_number_sequences_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_number_sequences_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT CK_number_sequences_doc_type CHECK (doc_type IN ('quotation','invoice','receipt','payment')),
        CONSTRAINT CK_number_sequences_reset_policy CHECK (reset_policy IN ('none','yearly','monthly')),
        CONSTRAINT UQ_number_sequences_scope UNIQUE (tenant_id, doc_type, branch_id)
    );
END
GO

IF OBJECT_ID('dbo.quotations', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.quotations (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_quotations PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        number NVARCHAR(80) NOT NULL,
        [date] DATE NOT NULL,
        valid_until DATE NULL,
        customer_id UNIQUEIDENTIFIER NOT NULL,
        status TINYINT NOT NULL CONSTRAINT DF_quotations_status DEFAULT 0,
        currency CHAR(3) NOT NULL CONSTRAINT DF_quotations_currency DEFAULT 'IDR',
        notes NVARCHAR(MAX) NULL,
        sub_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_quotations_sub_total DEFAULT 0,
        discount_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_quotations_discount_total DEFAULT 0,
        tax_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_quotations_tax_total DEFAULT 0,
        grand_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_quotations_grand_total DEFAULT 0,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_quotations_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_quotations_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_quotations_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_quotations_customer FOREIGN KEY (customer_id) REFERENCES dbo.customers(id),
        CONSTRAINT CK_quotations_status CHECK (status IN (0,1,2,3,4)),
        CONSTRAINT UQ_quotations_tenant_number UNIQUE (tenant_id, number)
    );
END
GO

IF OBJECT_ID('dbo.quotation_items', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.quotation_items (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_quotation_items PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        quotation_id UNIQUEIDENTIFIER NOT NULL,
        item_id UNIQUEIDENTIFIER NULL,
        name NVARCHAR(200) NOT NULL,
        description NVARCHAR(1000) NULL,
        qty DECIMAL(19,2) NOT NULL,
        uom NVARCHAR(20) NOT NULL,
        price DECIMAL(19,2) NOT NULL,
        discount DECIMAL(19,2) NOT NULL CONSTRAINT DF_quotation_items_discount DEFAULT 0,
        tax_id UNIQUEIDENTIFIER NULL,
        line_total AS CAST(((qty * price) - discount) AS DECIMAL(19,2)) PERSISTED,
        sort_order INT NOT NULL CONSTRAINT DF_quotation_items_sort_order DEFAULT 0,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_quotation_items_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_quotation_items_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_quotation_items_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_quotation_items_quotation FOREIGN KEY (quotation_id) REFERENCES dbo.quotations(id),
        CONSTRAINT FK_quotation_items_item FOREIGN KEY (item_id) REFERENCES dbo.items(id),
        CONSTRAINT FK_quotation_items_tax FOREIGN KEY (tax_id) REFERENCES dbo.taxes(id)
    );
END
GO

IF OBJECT_ID('dbo.invoices', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.invoices (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_invoices PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        number NVARCHAR(80) NOT NULL,
        [date] DATE NOT NULL,
        due_date DATE NOT NULL,
        customer_id UNIQUEIDENTIFIER NOT NULL,
        status TINYINT NOT NULL CONSTRAINT DF_invoices_status DEFAULT 0,
        currency CHAR(3) NOT NULL CONSTRAINT DF_invoices_currency DEFAULT 'IDR',
        notes NVARCHAR(MAX) NULL,
        sub_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_invoices_sub_total DEFAULT 0,
        discount_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_invoices_discount_total DEFAULT 0,
        tax_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_invoices_tax_total DEFAULT 0,
        grand_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_invoices_grand_total DEFAULT 0,
        paid_total DECIMAL(19,2) NOT NULL CONSTRAINT DF_invoices_paid_total DEFAULT 0,
        balance_total AS CAST((grand_total - paid_total) AS DECIMAL(19,2)) PERSISTED,
        source_quotation_id UNIQUEIDENTIFIER NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_invoices_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_invoices_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_invoices_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_invoices_customer FOREIGN KEY (customer_id) REFERENCES dbo.customers(id),
        CONSTRAINT FK_invoices_source_quotation FOREIGN KEY (source_quotation_id) REFERENCES dbo.quotations(id),
        CONSTRAINT CK_invoices_status CHECK (status IN (0,1,2,3,4)),
        CONSTRAINT UQ_invoices_tenant_number UNIQUE (tenant_id, number)
    );
END
GO

IF OBJECT_ID('dbo.invoice_items', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.invoice_items (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_invoice_items PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        invoice_id UNIQUEIDENTIFIER NOT NULL,
        item_id UNIQUEIDENTIFIER NULL,
        name NVARCHAR(200) NOT NULL,
        description NVARCHAR(1000) NULL,
        qty DECIMAL(19,2) NOT NULL,
        uom NVARCHAR(20) NOT NULL,
        price DECIMAL(19,2) NOT NULL,
        discount DECIMAL(19,2) NOT NULL CONSTRAINT DF_invoice_items_discount DEFAULT 0,
        tax_id UNIQUEIDENTIFIER NULL,
        line_total AS CAST(((qty * price) - discount) AS DECIMAL(19,2)) PERSISTED,
        sort_order INT NOT NULL CONSTRAINT DF_invoice_items_sort_order DEFAULT 0,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_invoice_items_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_invoice_items_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_invoice_items_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES dbo.invoices(id),
        CONSTRAINT FK_invoice_items_item FOREIGN KEY (item_id) REFERENCES dbo.items(id),
        CONSTRAINT FK_invoice_items_tax FOREIGN KEY (tax_id) REFERENCES dbo.taxes(id)
    );
END
GO

IF OBJECT_ID('dbo.payments', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.payments (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_payments PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        number NVARCHAR(80) NOT NULL,
        [date] DATE NOT NULL,
        customer_id UNIQUEIDENTIFIER NOT NULL,
        method NVARCHAR(20) NOT NULL,
        amount DECIMAL(19,2) NOT NULL,
        unapplied_amount DECIMAL(19,2) NOT NULL CONSTRAINT DF_payments_unapplied_amount DEFAULT 0,
        notes NVARCHAR(MAX) NULL,
        status TINYINT NOT NULL CONSTRAINT DF_payments_status DEFAULT 0,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_payments_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_payments_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_payments_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_payments_customer FOREIGN KEY (customer_id) REFERENCES dbo.customers(id),
        CONSTRAINT CK_payments_method CHECK (method IN ('cash','transfer','va','other')),
        CONSTRAINT CK_payments_status CHECK (status IN (0,1,2)),
        CONSTRAINT UQ_payments_tenant_number UNIQUE (tenant_id, number)
    );
END
GO

IF OBJECT_ID('dbo.payment_allocations', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.payment_allocations (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_payment_allocations PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        payment_id UNIQUEIDENTIFIER NOT NULL,
        invoice_id UNIQUEIDENTIFIER NOT NULL,
        allocated_amount DECIMAL(19,2) NOT NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_payment_allocations_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_payment_allocations_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_payment_allocations_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_payment_allocations_payment FOREIGN KEY (payment_id) REFERENCES dbo.payments(id),
        CONSTRAINT FK_payment_allocations_invoice FOREIGN KEY (invoice_id) REFERENCES dbo.invoices(id),
        CONSTRAINT CK_payment_allocations_amount CHECK (allocated_amount > 0)
    );
END
GO

IF OBJECT_ID('dbo.documents', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.documents (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_documents PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        owner_table NVARCHAR(100) NOT NULL,
        owner_id UNIQUEIDENTIFIER NOT NULL,
        filename NVARCHAR(255) NOT NULL,
        mime NVARCHAR(100) NOT NULL,
        size BIGINT NOT NULL,
        [path] NVARCHAR(500) NOT NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_documents_created_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_documents_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id)
    );
END
GO

IF OBJECT_ID('dbo.audit_logs', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.audit_logs (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_audit_logs PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        user_id UNIQUEIDENTIFIER NULL,
        action NVARCHAR(100) NOT NULL,
        entity NVARCHAR(100) NOT NULL,
        entity_id UNIQUEIDENTIFIER NULL,
        old_data NVARCHAR(MAX) NULL,
        new_data NVARCHAR(MAX) NULL,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_audit_logs_created_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_audit_logs_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_audit_logs_user FOREIGN KEY (user_id) REFERENCES dbo.users(id)
    );
END
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_users_tenant_role' AND object_id = OBJECT_ID('dbo.users'))
    CREATE INDEX IX_users_tenant_role ON dbo.users (tenant_id, role, is_active);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_customers_tenant_name' AND object_id = OBJECT_ID('dbo.customers'))
    CREATE INDEX IX_customers_tenant_name ON dbo.customers (tenant_id, name);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_items_tenant_name' AND object_id = OBJECT_ID('dbo.items'))
    CREATE INDEX IX_items_tenant_name ON dbo.items (tenant_id, name);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_taxes_tenant_active' AND object_id = OBJECT_ID('dbo.taxes'))
    CREATE INDEX IX_taxes_tenant_active ON dbo.taxes (tenant_id, is_active);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_quotations_tenant_date' AND object_id = OBJECT_ID('dbo.quotations'))
    CREATE INDEX IX_quotations_tenant_date ON dbo.quotations (tenant_id, [date], status);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_quotation_items_quotation' AND object_id = OBJECT_ID('dbo.quotation_items'))
    CREATE INDEX IX_quotation_items_quotation ON dbo.quotation_items (quotation_id, sort_order);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_invoices_tenant_due_status' AND object_id = OBJECT_ID('dbo.invoices'))
    CREATE INDEX IX_invoices_tenant_due_status ON dbo.invoices (tenant_id, due_date, status);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_invoice_items_invoice' AND object_id = OBJECT_ID('dbo.invoice_items'))
    CREATE INDEX IX_invoice_items_invoice ON dbo.invoice_items (invoice_id, sort_order);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_payments_tenant_date_status' AND object_id = OBJECT_ID('dbo.payments'))
    CREATE INDEX IX_payments_tenant_date_status ON dbo.payments (tenant_id, [date], status);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_payment_allocations_payment' AND object_id = OBJECT_ID('dbo.payment_allocations'))
    CREATE INDEX IX_payment_allocations_payment ON dbo.payment_allocations (payment_id);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_payment_allocations_invoice' AND object_id = OBJECT_ID('dbo.payment_allocations'))
    CREATE INDEX IX_payment_allocations_invoice ON dbo.payment_allocations (invoice_id);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_documents_tenant_owner' AND object_id = OBJECT_ID('dbo.documents'))
    CREATE INDEX IX_documents_tenant_owner ON dbo.documents (tenant_id, owner_table, owner_id);
GO
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_audit_logs_tenant_entity' AND object_id = OBJECT_ID('dbo.audit_logs'))
    CREATE INDEX IX_audit_logs_tenant_entity ON dbo.audit_logs (tenant_id, entity, created_at DESC);
GO
