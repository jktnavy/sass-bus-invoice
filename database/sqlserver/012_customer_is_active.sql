SET NOCOUNT ON;
SET XACT_ABORT ON;

IF COL_LENGTH('dbo.customers', 'is_active') IS NULL
BEGIN
    ALTER TABLE dbo.customers
        ADD is_active BIT NOT NULL
            CONSTRAINT DF_customers_is_active DEFAULT (1);
END
GO

IF NOT EXISTS (
    SELECT 1
    FROM sys.indexes
    WHERE object_id = OBJECT_ID('dbo.customers')
      AND name = 'IX_customers_tenant_active'
)
BEGIN
    CREATE INDEX IX_customers_tenant_active
        ON dbo.customers (tenant_id, is_active, name);
END
GO
