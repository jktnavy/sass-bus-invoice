<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Tenant;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class SaasOverview extends Page
{
    use WithPagination;

    protected string $view = 'filament.pages.saas-overview';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'SaaS Monitor';

    protected static ?string $title = 'SaaS Monitoring';

    protected static ?int $navigationSort = 1;

    public string $search = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'superadmin';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getTenantRowsProperty(): LengthAwarePaginator
    {
        $userCounts = DB::table('users')
            ->selectRaw('tenant_id, COUNT(*) AS total_users, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_users')
            ->groupBy('tenant_id');

        $quotationCounts = DB::table('quotations')
            ->selectRaw('tenant_id, COUNT(*) AS total_quotations')
            ->groupBy('tenant_id');

        $invoiceCounts = DB::table('invoices')
            ->selectRaw('tenant_id, COUNT(*) AS total_invoices, SUM(CASE WHEN balance_total > 0 THEN 1 ELSE 0 END) AS outstanding_invoices')
            ->groupBy('tenant_id');

        $paymentCounts = DB::table('payments')
            ->selectRaw('tenant_id, COUNT(*) AS total_payments')
            ->groupBy('tenant_id');

        return Tenant::query()
            ->leftJoinSub($userCounts, 'u', 'u.tenant_id', '=', 'tenants.id')
            ->leftJoinSub($quotationCounts, 'q', 'q.tenant_id', '=', 'tenants.id')
            ->leftJoinSub($invoiceCounts, 'i', 'i.tenant_id', '=', 'tenants.id')
            ->leftJoinSub($paymentCounts, 'p', 'p.tenant_id', '=', 'tenants.id')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('tenants.name', 'like', '%'.$this->search.'%')
                        ->orWhere('tenants.code', 'like', '%'.$this->search.'%')
                        ->orWhere('tenants.email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('tenants.created_at')
            ->select([
                'tenants.id',
                'tenants.code',
                'tenants.name',
                'tenants.email',
                'tenants.phone',
                'tenants.created_at',
                DB::raw('COALESCE(u.total_users, 0) AS total_users'),
                DB::raw('COALESCE(u.active_users, 0) AS active_users'),
                DB::raw('COALESCE(q.total_quotations, 0) AS total_quotations'),
                DB::raw('COALESCE(i.total_invoices, 0) AS total_invoices'),
                DB::raw('COALESCE(i.outstanding_invoices, 0) AS outstanding_invoices'),
                DB::raw('COALESCE(p.total_payments, 0) AS total_payments'),
            ])
            ->paginate(10);
    }

    public function getSummaryProperty(): array
    {
        return [
            'tenants' => Tenant::query()->count(),
            'users' => User::query()->count(),
            'quotations' => Quotation::query()->count(),
            'invoices' => Invoice::query()->count(),
            'outstanding_invoices' => Invoice::query()->where('balance_total', '>', 0)->count(),
            'payments' => Payment::query()->count(),
        ];
    }
}

