<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Throwable;

class FriendlyExceptionMessage
{
    public static function from(Throwable $exception): string
    {
        if ($exception instanceof QueryException) {
            return self::fromQueryException($exception);
        }

        $message = mb_strtolower($exception->getMessage());

        if (str_contains($message, 'maximum execution time')) {
            return 'Proses terlalu lama (timeout). Coba ulangi atau kecilkan ukuran logo/gambar.';
        }

        if (str_contains($message, 'permission denied') || str_contains($message, 'failed to open stream')) {
            return 'Gagal mengakses file storage. Periksa permission folder storage/.';
        }

        if (str_contains($message, 'dompdf') || str_contains($message, 'image') || str_contains($message, 'gd')) {
            return 'Gagal memproses PDF (gambar/logo tidak valid atau terlalu besar).';
        }

        if (filled($exception->getMessage())) {
            return 'Terjadi kesalahan: '.$exception->getMessage();
        }

        return 'Terjadi kesalahan saat menyimpan data. Silakan cek input lalu coba lagi.';
    }

    private static function fromQueryException(QueryException $exception): string
    {
        $message = mb_strtolower($exception->getMessage());

        if (str_contains($message, 'fk_quotations_customer')) {
            return 'Customer tidak bisa dihapus karena masih direferensikan dokumen Quotation. Status Void tidak menghapus dokumen.';
        }

        if (str_contains($message, 'fk_invoices_customer')) {
            return 'Customer tidak bisa dihapus karena masih direferensikan dokumen Invoice. Status Void/Reversed tidak menghapus dokumen.';
        }

        if (str_contains($message, 'duplicate key') || str_contains($message, 'unique key constraint')) {
            return 'Data duplikat terdeteksi. Gunakan nilai unik (misalnya kode/nomor berbeda).';
        }

        if (str_contains($message, 'invalid column name') || str_contains($message, 'invalid object name')) {
            return 'Struktur database belum sinkron dengan aplikasi. Jalankan seluruh file SQL terbaru di folder database/sqlserver.';
        }

        if (str_contains($message, 'foreign key') || str_contains($message, 'reference constraint')) {
            return 'Data tidak bisa diproses karena masih terhubung ke data lain.';
        }

        if (str_contains($message, 'cannot insert the value null')) {
            return 'Ada field wajib yang belum terisi.';
        }

        return 'Gagal menyimpan data ke database. Silakan cek input dan coba lagi.';
    }
}
