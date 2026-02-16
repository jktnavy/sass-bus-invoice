<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class IndonesianFormat
{
    public static function dateLong(CarbonInterface|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        $value = $date instanceof CarbonInterface ? $date : Carbon::parse($date);

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $value->day.' '.$months[$value->month].' '.$value->year;
    }

    public static function rupiah(int|float|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return 'Rp '.number_format($value, 0, ',', '.').',-';
    }
}

