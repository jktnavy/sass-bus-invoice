SET NOCOUNT ON;
SET XACT_ABORT ON;

BEGIN TRANSACTION;

-- =========================
-- Quotation additional fields for letter-style PDF format
-- =========================
IF COL_LENGTH('dbo.quotations', 'city') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD city NVARCHAR(100) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'recipient_title_line1') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD recipient_title_line1 NVARCHAR(255) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'recipient_company_line2') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD recipient_company_line2 NVARCHAR(255) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'attachment_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD attachment_text NVARCHAR(255) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'subject_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD subject_text NVARCHAR(255) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'opening_paragraph') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD opening_paragraph NVARCHAR(MAX) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'vehicle_type_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD vehicle_type_text NVARCHAR(255) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'service_route_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD service_route_text NVARCHAR(500) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'fare_text_label') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD fare_text_label NVARCHAR(255) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'fare_amount') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD fare_amount DECIMAL(19,2) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'usage_date') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD usage_date DATE NULL;
END;

IF COL_LENGTH('dbo.quotations', 'included_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD included_text NVARCHAR(MAX) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'facilities_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD facilities_text NVARCHAR(MAX) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'payment_method_text') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD payment_method_text NVARCHAR(MAX) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'closing_paragraph') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD closing_paragraph NVARCHAR(MAX) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'signatory_name') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD signatory_name NVARCHAR(150) NULL;
END;

IF COL_LENGTH('dbo.quotations', 'signatory_title') IS NULL
BEGIN
    ALTER TABLE dbo.quotations ADD signatory_title NVARCHAR(150) NULL;
END;

-- Defaults for new rows
IF OBJECT_ID('DF_quotations_city', 'D') IS NULL
BEGIN
    ALTER TABLE dbo.quotations
        ADD CONSTRAINT DF_quotations_city DEFAULT (N'Jakarta') FOR city;
END;

IF OBJECT_ID('DF_quotations_attachment_text', 'D') IS NULL
BEGIN
    ALTER TABLE dbo.quotations
        ADD CONSTRAINT DF_quotations_attachment_text DEFAULT (N'-') FOR attachment_text;
END;

IF OBJECT_ID('DF_quotations_subject_text', 'D') IS NULL
BEGIN
    ALTER TABLE dbo.quotations
        ADD CONSTRAINT DF_quotations_subject_text DEFAULT (N'Penawaran Sewa Kendaraan') FOR subject_text;
END;

IF OBJECT_ID('DF_quotations_fare_text_label', 'D') IS NULL
BEGIN
    ALTER TABLE dbo.quotations
        ADD CONSTRAINT DF_quotations_fare_text_label DEFAULT (N'Harga sewa bus') FOR fare_text_label;
END;

-- Backfill existing rows if empty
IF COL_LENGTH('dbo.quotations', 'city') IS NOT NULL
    AND COL_LENGTH('dbo.quotations', 'attachment_text') IS NOT NULL
    AND COL_LENGTH('dbo.quotations', 'subject_text') IS NOT NULL
    AND COL_LENGTH('dbo.quotations', 'fare_text_label') IS NOT NULL
    AND COL_LENGTH('dbo.quotations', 'opening_paragraph') IS NOT NULL
    AND COL_LENGTH('dbo.quotations', 'closing_paragraph') IS NOT NULL
BEGIN
    EXEC sys.sp_executesql N'
        UPDATE dbo.quotations
        SET city = ISNULL(NULLIF(city, ''''), N''Jakarta''),
            attachment_text = ISNULL(NULLIF(attachment_text, ''''), N''-''),
            subject_text = ISNULL(NULLIF(subject_text, ''''), N''Penawaran Sewa Kendaraan''),
            fare_text_label = ISNULL(NULLIF(fare_text_label, ''''), N''Harga sewa bus''),
            opening_paragraph = ISNULL(NULLIF(opening_paragraph, ''''), N''Kami dari PT. Sumber Tali Asih (STA Trans) dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:''),
            closing_paragraph = ISNULL(NULLIF(closing_paragraph, ''''), N''Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.'')
        WHERE 1 = 1;
    ';
END;

-- =========================
-- Documents metadata compatibility
-- =========================
IF COL_LENGTH('dbo.documents', 'storage_path') IS NULL
BEGIN
    ALTER TABLE dbo.documents ADD storage_path NVARCHAR(500) NULL;
END;

IF COL_LENGTH('dbo.documents', 'storage_path') IS NOT NULL
BEGIN
    EXEC sys.sp_executesql N'
        UPDATE dbo.documents
        SET storage_path = path
        WHERE storage_path IS NULL;
    ';
END;

COMMIT TRANSACTION;
