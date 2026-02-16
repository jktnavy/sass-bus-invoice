IF DB_ID('bus_invoice_db') IS NULL
BEGIN
    CREATE DATABASE bus_invoice_db;
END
GO

USE bus_invoice_db;
GO

IF EXISTS (SELECT 1 FROM sys.security_policies WHERE name = 'TenantIsolationPolicy' AND schema_id = SCHEMA_ID('sec'))
BEGIN
    ALTER SECURITY POLICY sec.TenantIsolationPolicy WITH (STATE = OFF);
    DROP SECURITY POLICY sec.TenantIsolationPolicy;
END
GO

IF OBJECT_ID('dbo.vw_ar_aging', 'V') IS NOT NULL DROP VIEW dbo.vw_ar_aging;
GO

IF OBJECT_ID('dbo.trg_payment_allocations_sync', 'TR') IS NOT NULL DROP TRIGGER dbo.trg_payment_allocations_sync;
IF OBJECT_ID('dbo.trg_invoice_items_recalc', 'TR') IS NOT NULL DROP TRIGGER dbo.trg_invoice_items_recalc;
IF OBJECT_ID('dbo.trg_quotation_items_recalc', 'TR') IS NOT NULL DROP TRIGGER dbo.trg_quotation_items_recalc;
GO

IF OBJECT_ID('dbo.sp_allocate_payment', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_allocate_payment;
IF OBJECT_ID('dbo.sp_reverse_payment', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_reverse_payment;
IF OBJECT_ID('dbo.sp_post_payment', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_post_payment;
IF OBJECT_ID('dbo.sp_convert_quotation_to_invoice', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_convert_quotation_to_invoice;
IF OBJECT_ID('dbo.sp_recalc_invoice_totals', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_recalc_invoice_totals;
IF OBJECT_ID('dbo.sp_recalc_quotation_totals', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_recalc_quotation_totals;
IF OBJECT_ID('dbo.sp_next_number', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_next_number;
IF OBJECT_ID('sec.sp_set_tenant', 'P') IS NOT NULL DROP PROCEDURE sec.sp_set_tenant;
GO

IF OBJECT_ID('sec.fn_tenantPredicate', 'IF') IS NOT NULL DROP FUNCTION sec.fn_tenantPredicate;
GO

IF OBJECT_ID('dbo.audit_logs', 'U') IS NOT NULL DROP TABLE dbo.audit_logs;
IF OBJECT_ID('dbo.documents', 'U') IS NOT NULL DROP TABLE dbo.documents;
IF OBJECT_ID('dbo.payment_allocations', 'U') IS NOT NULL DROP TABLE dbo.payment_allocations;
IF OBJECT_ID('dbo.payments', 'U') IS NOT NULL DROP TABLE dbo.payments;
IF OBJECT_ID('dbo.invoice_items', 'U') IS NOT NULL DROP TABLE dbo.invoice_items;
IF OBJECT_ID('dbo.invoices', 'U') IS NOT NULL DROP TABLE dbo.invoices;
IF OBJECT_ID('dbo.quotation_items', 'U') IS NOT NULL DROP TABLE dbo.quotation_items;
IF OBJECT_ID('dbo.quotations', 'U') IS NOT NULL DROP TABLE dbo.quotations;
IF OBJECT_ID('dbo.number_sequences', 'U') IS NOT NULL DROP TABLE dbo.number_sequences;
IF OBJECT_ID('dbo.items', 'U') IS NOT NULL DROP TABLE dbo.items;
IF OBJECT_ID('dbo.taxes', 'U') IS NOT NULL DROP TABLE dbo.taxes;
IF OBJECT_ID('dbo.customers', 'U') IS NOT NULL DROP TABLE dbo.customers;
IF OBJECT_ID('dbo.users', 'U') IS NOT NULL DROP TABLE dbo.users;
IF OBJECT_ID('dbo.tenants', 'U') IS NOT NULL DROP TABLE dbo.tenants;
GO
