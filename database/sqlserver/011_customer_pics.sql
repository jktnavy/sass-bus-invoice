SET NOCOUNT ON;
SET XACT_ABORT ON;
GO

USE bus_invoice_db;
GO

BEGIN TRANSACTION;

IF OBJECT_ID('dbo.customer_pics', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.customer_pics (
        id UNIQUEIDENTIFIER NOT NULL CONSTRAINT PK_customer_pics PRIMARY KEY DEFAULT NEWSEQUENTIALID(),
        tenant_id UNIQUEIDENTIFIER NOT NULL,
        customer_id UNIQUEIDENTIFIER NOT NULL,
        name NVARCHAR(150) NOT NULL,
        phone NVARCHAR(30) NULL,
        email NVARCHAR(255) NULL,
        position NVARCHAR(100) NULL,
        notes NVARCHAR(500) NULL,
        is_primary BIT NOT NULL CONSTRAINT DF_customer_pics_is_primary DEFAULT 0,
        created_at DATETIME2(3) NOT NULL CONSTRAINT DF_customer_pics_created_at DEFAULT SYSUTCDATETIME(),
        updated_at DATETIME2(3) NOT NULL CONSTRAINT DF_customer_pics_updated_at DEFAULT SYSUTCDATETIME(),
        CONSTRAINT FK_customer_pics_tenant FOREIGN KEY (tenant_id) REFERENCES dbo.tenants(id),
        CONSTRAINT FK_customer_pics_customer FOREIGN KEY (customer_id) REFERENCES dbo.customers(id)
    );
END;

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_customer_pics_customer' AND object_id = OBJECT_ID('dbo.customer_pics'))
BEGIN
    CREATE INDEX IX_customer_pics_customer ON dbo.customer_pics (customer_id, is_primary, name);
END;

COMMIT TRANSACTION;
GO

-- Add RLS predicates to existing policy
IF OBJECT_ID('sec.TenantIsolationPolicy', 'SP') IS NOT NULL
AND OBJECT_ID('dbo.customer_pics', 'U') IS NOT NULL
AND NOT EXISTS (
    SELECT 1
    FROM sys.security_predicates sp
    INNER JOIN sys.tables t ON t.object_id = sp.target_object_id
    WHERE sp.object_id = OBJECT_ID('sec.TenantIsolationPolicy')
      AND t.name = 'customer_pics'
)
BEGIN
    EXEC('
        ALTER SECURITY POLICY sec.TenantIsolationPolicy
        ADD FILTER PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.customer_pics,
        ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.customer_pics AFTER INSERT,
        ADD BLOCK PREDICATE sec.fn_tenantPredicate(tenant_id) ON dbo.customer_pics AFTER UPDATE;
    ');
END;
GO

