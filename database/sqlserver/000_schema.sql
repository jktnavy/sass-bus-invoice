IF DB_ID('bus_invoice_db') IS NULL
BEGIN
    CREATE DATABASE bus_invoice_db;
END
GO

USE bus_invoice_db;
GO

IF SCHEMA_ID('sec') IS NULL
    EXEC ('CREATE SCHEMA sec');
GO
