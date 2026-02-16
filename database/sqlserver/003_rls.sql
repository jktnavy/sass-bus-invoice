SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
GO

USE bus_invoice_db;
GO

IF SCHEMA_ID('sec') IS NULL EXEC ('CREATE SCHEMA sec');
GO

CREATE PROCEDURE sec.sp_set_tenant
    @tenant_id UNIQUEIDENTIFIER
AS
BEGIN
    SET NOCOUNT ON;

    EXEC sys.sp_set_session_context @key = N'tenant_id', @value = @tenant_id, @read_only = 0;
END
GO

CREATE FUNCTION sec.fn_tenantPredicate(@tenant_id UNIQUEIDENTIFIER)
RETURNS TABLE
WITH SCHEMABINDING
AS
RETURN
    SELECT 1 AS fn_tenantPredicate_result
    WHERE CAST(SESSION_CONTEXT(N'tenant_id') AS UNIQUEIDENTIFIER) = @tenant_id;
GO

CREATE FUNCTION sec.fn_userPredicate(@tenant_id UNIQUEIDENTIFIER)
RETURNS TABLE
WITH SCHEMABINDING
AS
RETURN
    SELECT 1 AS fn_userPredicate_result
    WHERE CAST(SESSION_CONTEXT(N'tenant_id') AS UNIQUEIDENTIFIER) = @tenant_id
       OR SESSION_CONTEXT(N'tenant_id') IS NULL;
GO

IF EXISTS (SELECT 1 FROM sys.security_policies WHERE name = 'TenantIsolationPolicy')
BEGIN
    EXEC('ALTER SECURITY POLICY sec.TenantIsolationPolicy WITH (STATE = OFF);');
    EXEC('DROP SECURITY POLICY sec.TenantIsolationPolicy;');
END
GO

CREATE SECURITY POLICY sec.TenantIsolationPolicy
ADD FILTER PREDICATE sec.fn_userPredicate(tenant_id) ON dbo.users,
ADD BLOCK PREDICATE sec.fn_userPredicate(tenant_id) ON dbo.users AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_userPredicate(tenant_id) ON dbo.users AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.customers,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.customers AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.customers AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.items,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.items AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.items AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.taxes,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.taxes AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.taxes AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.number_sequences,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.number_sequences AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.number_sequences AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.quotations,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.quotations AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.quotations AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.quotation_items,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.quotation_items AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.quotation_items AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.invoices,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.invoices AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.invoices AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.invoice_items,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.invoice_items AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.invoice_items AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.payments,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.payments AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.payments AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.payment_allocations,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.payment_allocations AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.payment_allocations AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.documents,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.documents AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.documents AFTER UPDATE,
ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.audit_logs,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.audit_logs AFTER INSERT,
ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.audit_logs AFTER UPDATE
WITH (STATE = ON);
GO
