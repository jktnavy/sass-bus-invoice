# Bus Invoice SaaS (Laravel 12 + Filament v5 + SQL Server)

Aplikasi SaaS invoicing multi-tenant untuk perusahaan bus pariwisata.

## Stack
- Laravel `12.x`
- FilamentPHP `v5`
- Laravel Boost
- Database: **Microsoft SQL Server (`sqlsrv`) only**

## Konfigurasi DB
Gunakan environment berikut:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=bus_invoice_db
DB_USERNAME=sa
DB_PASSWORD=Password.123
```

## Setup Proyek
1. Install dependency:
```bash
composer install
npm install
```

2. Salin env dan generate key:
```bash
cp .env.example .env
php artisan key:generate
```

3. Jalankan SQL Server scripts di SSMS (wajib, urut):
- `database/sqlserver/001_ddl.sql`
- `database/sqlserver/002_logic.sql`
- `database/sqlserver/003_rls.sql`
- `database/sqlserver/010_alter_quotation_format_fields.sql`
- `database/sqlserver/900_seed_demo.sql` (opsional demo)

4. Jalankan migration Laravel internal saja (cache/jobs):
```bash
php artisan migrate
```

5. Jalankan app:
```bash
php artisan serve
```

Akses panel: `http://127.0.0.1:8000/admin`

## Tenant Isolation (Defense-in-Depth)
Aplikasi pakai 3 lapis isolasi tenant:
1. `tenant_id` di semua tabel bisnis
2. SQL Server RLS (`SESSION_CONTEXT('tenant_id')`)
3. Global scope Eloquent (`BelongsToTenant`)

Middleware `SetTenantSessionContext` mengeksekusi:

```sql
EXEC sec.sp_set_tenant @tenant_id = ?
```

setiap request authenticated di panel Filament.

## Modul MVP
- Master data: Customers, Items, Taxes, Number Sequences
- Dokumen: Quotations (+ items), Invoices (+ items)
- Pembayaran: Payments (+ allocations)
- PDF: Generate quotation/invoice, simpan metadata ke `documents`
- Audit log: create/update/status/void/post/reverse/convert/document_generate
- Roles minimal:
  - `admin`: master + documents + payments
  - `sales`: quotation
  - `finance`: invoice + payment

## Stored Procedure yang Dipakai Aplikasi
- `dbo.sp_next_number`
- `dbo.sp_recalc_quotation_totals`
- `dbo.sp_recalc_invoice_totals`
- `dbo.sp_convert_quotation_to_invoice`
- `dbo.sp_post_payment`
- `dbo.sp_reverse_payment`
- `dbo.sp_allocate_payment`

## Quotation PDF Format
- Template surat quotation mengikuti format DOCX referensi:
  `database/word/Quotation Partai Golkar - QUOT.XI.8010.docx`
- Blade template aktif:
  `resources/views/pdf/quotation_sta.blade.php`
- Field surat diisi dari form quotation section **Format Surat Penawaran**:
  - Header surat (`city`, `date`, `number`)
  - Penerima (`recipient_title_line1`, `recipient_company_line2`)
  - Lampiran/Perihal (`attachment_text`, `subject_text`)
  - Paragraf pembuka & penutup (`opening_paragraph`, `closing_paragraph`)
  - Detail penawaran (jenis kendaraan, rute, tarif, tanggal pemakaian, fasilitas, metode pembayaran)
  - Penandatangan (`signatory_name`, `signatory_title`)
- Format output otomatis:
  - Tanggal Indonesia: `4 November 2025`
  - Rupiah: `Rp 2.650.000,-`
  - Terbilang: `( dua juta enam ratus lima puluh ribu rupiah )`
- Generate PDF:
  1. Buka `Documents -> Quotations` -> Edit quotation.
  2. Klik `Generate PDF`.
  3. Setelah sukses, gunakan tombol `Open PDF` / `Download PDF` dari notifikasi atau table action.
- Helper formatting:
  - `app/Support/IndonesianFormat.php`
  - `app/Support/Terbilang.php`

## Seed Demo
`900_seed_demo.sql` membuat:
- 2 tenant
- Tenant 1: 2 user (`admin`, `finance`)
- Tenant 2: 1 user (`admin`)
- Password demo: `Password.123`

## Membuat User Pertama via CLI

```bash
php artisan app:create-initial-user "TNT-01" "Admin Tenant 1" "admin1@company.com" "Password.123" admin
```

## Checklist Uji Manual
1. Login tenant 1 (admin/sales) -> create customer -> create quotation + items -> total terhitung (SP + trigger).
2. Ubah status quotation accepted -> klik `Convert to Invoice` -> nomor invoice auto dari `sp_next_number`.
3. Create payment draft -> `Post` -> `unapplied_amount = amount`.
4. Allocate partial ke invoice -> status invoice jadi `partial`.
5. Allocate sisa -> status invoice jadi `paid`.
6. Login tenant 2 -> list data tenant 1 tidak terlihat (RLS + scope).
7. Audit logs terisi untuk aksi penting.
8. Generate PDF quotation/invoice -> metadata masuk `documents`.

## Catatan
- Struktur bisnis utama **tidak dibuat lewat migration Laravel**, tetapi lewat T-SQL di `database/sqlserver/`.
- Disarankan pakai SQL login non-`sa` untuk production.
