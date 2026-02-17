SET NOCOUNT ON;
SET XACT_ABORT ON;

IF COL_LENGTH('dbo.quotations', 'usage_end_date') IS NULL
BEGIN
    ALTER TABLE dbo.quotations
        ADD usage_end_date DATE NULL;
END
GO
