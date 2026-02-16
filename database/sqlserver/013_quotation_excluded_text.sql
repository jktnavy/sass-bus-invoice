SET NOCOUNT ON;
SET XACT_ABORT ON;

IF COL_LENGTH('dbo.quotations', 'excluded_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations
        ADD excluded_text NVARCHAR(MAX) NULL;
END
GO
