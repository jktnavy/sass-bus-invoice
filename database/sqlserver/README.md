# SQL Server Setup (SSMS)

Gunakan konfigurasi koneksi aplikasi dengan placeholder berikut:

```env
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=bus_invoice_db
DB_USERNAME=your_sqlsrv_username
DB_PASSWORD=your_sqlsrv_password
```

Jalankan file SQL berikut **berurutan** di database `bus_invoice_db`:

1. `001_ddl.sql`
2. `002_logic.sql`
3. `003_rls.sql`
4. `010_alter_quotation_format_fields.sql`
5. `900_seed_demo.sql` (opsional demo)

## Catatan penting
- Login SQL Server untuk aplikasi disarankan **bukan** `sa` di production.
- Middleware Laravel akan men-set tenant context setiap request:
  `EXEC sec.sp_set_tenant @tenant_id = '...'`
- Untuk verifikasi manual RLS di SSMS:
  1. `EXEC sec.sp_set_tenant @tenant_id = '<tenant1-guid>'`
  2. `SELECT * FROM dbo.customers` (hanya data tenant 1)
  3. Ganti tenant_id ke tenant 2 dan ulangi query.
