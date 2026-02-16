<?php

namespace App\Filament\Pages;

use App\Models\Quotation;
use App\Support\RoleHelper;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class LaporanQuotationVsInvoice extends Page
{
    use WithPagination;

    protected string $view = 'filament.pages.laporan-quotation-vs-invoice';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $title = 'Laporan Quotation vs Invoice';

    protected static ?int $navigationSort = 2;

    public string $statusFilter = 'all';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public static function canAccess(): bool
    {
        return RoleHelper::hasAnyRole(auth()->user(), ['admin']);
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        $query = Quotation::query()
            ->with(['customer', 'invoices' => fn ($q) => $q->orderByDesc('created_at')]);

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if ($this->statusFilter === 'uninvoiced') {
            $query->doesntHave('invoices');
        }

        if ($this->statusFilter === 'invoiced') {
            $query->has('invoices');
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('date')
            ->paginate(10);
    }

    public function getTotalQuotationCountProperty(): int
    {
        return Quotation::query()->count();
    }

    public function getTotalInvoicedCountProperty(): int
    {
        return Quotation::query()->has('invoices')->count();
    }

    public function getTotalUninvoicedCountProperty(): int
    {
        return Quotation::query()->doesntHave('invoices')->count();
    }
}
