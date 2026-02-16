USE bus_invoice_db;
GO

SET NOCOUNT ON;

DECLARE @tenant1 UNIQUEIDENTIFIER = NEWID();
DECLARE @tenant2 UNIQUEIDENTIFIER = NEWID();
DECLARE @tax1 UNIQUEIDENTIFIER = NEWID();
DECLARE @tax2 UNIQUEIDENTIFIER = NEWID();

INSERT INTO dbo.tenants (id, code, name, email, phone)
VALUES
(@tenant1, 'TNT-01', 'PT Bus Wisata Nusantara', 'admin@tenant1.local', '0811111111'),
(@tenant2, 'TNT-02', 'PT Pariwisata Sejahtera', 'admin@tenant2.local', '0822222222');

EXEC sys.sp_set_session_context @key = N'tenant_id', @value = @tenant1, @read_only = 0;

INSERT INTO dbo.users (tenant_id, name, email, password_hash, role, is_active)
VALUES
(@tenant1, 'Tenant1 Admin', 'admin1@example.com', '$2y$10$4qn9VXQ/iZJa/YmaYnRK9OPROhyM/NLGDo/LrYOUGEiQcAarbmpGS', 'admin', 1),
(@tenant1, 'Tenant1 Finance', 'finance1@example.com', '$2y$10$4qn9VXQ/iZJa/YmaYnRK9OPROhyM/NLGDo/LrYOUGEiQcAarbmpGS', 'finance', 1);

INSERT INTO dbo.taxes (id, tenant_id, name, rate, applies_to)
VALUES (@tax1, @tenant1, 'PPN 11%', 11, 'document');

INSERT INTO dbo.number_sequences (tenant_id, doc_type, prefix, suffix, padding, current_value, reset_policy)
VALUES
(@tenant1, 'quotation', 'QT-1-', NULL, 5, 0, 'none'),
(@tenant1, 'invoice', 'INV-1-', NULL, 5, 0, 'none'),
(@tenant1, 'payment', 'PAY-1-', NULL, 5, 0, 'none');

INSERT INTO dbo.customers (tenant_id, code, name, payment_terms_days, email)
VALUES (@tenant1, 'CUST-001', 'PT Pelanggan Tenant 1', 14, 'cust1@example.com');

INSERT INTO dbo.items (tenant_id, code, name, type, uom, default_price, tax_id)
VALUES (@tenant1, 'SVC-TRIP', 'Sewa Bus Pariwisata', 'service', 'trip', 3500000, @tax1);

EXEC sys.sp_set_session_context @key = N'tenant_id', @value = @tenant2, @read_only = 0;

INSERT INTO dbo.users (tenant_id, name, email, password_hash, role, is_active)
VALUES (@tenant2, 'Tenant2 Admin', 'admin2@example.com', '$2y$10$4qn9VXQ/iZJa/YmaYnRK9OPROhyM/NLGDo/LrYOUGEiQcAarbmpGS', 'admin', 1);

INSERT INTO dbo.taxes (id, tenant_id, name, rate, applies_to)
VALUES (@tax2, @tenant2, 'PPN 11%', 11, 'document');

INSERT INTO dbo.number_sequences (tenant_id, doc_type, prefix, suffix, padding, current_value, reset_policy)
VALUES
(@tenant2, 'quotation', 'QT-2-', NULL, 5, 0, 'none'),
(@tenant2, 'invoice', 'INV-2-', NULL, 5, 0, 'none'),
(@tenant2, 'payment', 'PAY-2-', NULL, 5, 0, 'none');

INSERT INTO dbo.customers (tenant_id, code, name, payment_terms_days, email)
VALUES (@tenant2, 'CUST-001', 'PT Pelanggan Tenant 2', 14, 'cust2@example.com');

INSERT INTO dbo.items (tenant_id, code, name, type, uom, default_price, tax_id)
VALUES (@tenant2, 'SVC-TRIP', 'Sewa Bus Pariwisata', 'service', 'trip', 4500000, @tax2);

EXEC sys.sp_set_session_context @key = N'tenant_id', @value = NULL, @read_only = 0;

PRINT 'Demo seed complete. Password seluruh user demo: Password.123';
