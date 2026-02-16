<x-filament-panels::page>
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Tenants</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($this->summary['tenants']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Users</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($this->summary['users']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Quotations</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($this->summary['quotations']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Invoices</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($this->summary['invoices']) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-950/30">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Outstanding</p>
                <p class="mt-2 text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($this->summary['outstanding_invoices']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Payments</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($this->summary['payments']) }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Cari Tenant</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nama, kode, email tenant..."
                        class="w-full rounded-lg border-slate-300 text-sm dark:border-white/20 dark:bg-gray-800"
                    />
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <a href="{{ route('filament.admin.resources.tenants.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:border-white/20 dark:hover:bg-white/5">
                        Manage Tenants
                    </a>
                    <a href="{{ route('filament.admin.resources.users.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:border-white/20 dark:hover:bg-white/5">
                        Manage Users
                    </a>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Tenant</th>
                            <th class="px-4 py-3 text-left font-semibold">Kontak</th>
                            <th class="px-4 py-3 text-right font-semibold">Users</th>
                            <th class="px-4 py-3 text-right font-semibold">Quotations</th>
                            <th class="px-4 py-3 text-right font-semibold">Invoices</th>
                            <th class="px-4 py-3 text-right font-semibold">Outstanding</th>
                            <th class="px-4 py-3 text-right font-semibold">Payments</th>
                            <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($this->tenantRows as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->code }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>{{ $row->email ?: '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->phone ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">{{ number_format((int) $row->active_users) }} / {{ number_format((int) $row->total_users) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((int) $row->total_quotations) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((int) $row->total_invoices) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span @class([
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' => (int) $row->outstanding_invoices === 0,
                                        'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' => (int) $row->outstanding_invoices > 0,
                                    ])>
                                        {{ number_format((int) $row->outstanding_invoices) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">{{ number_format((int) $row->total_payments) }}</td>
                                <td class="px-4 py-3">
                                    <a
                                        href="{{ route('filament.admin.resources.tenants.edit', ['record' => $row->id]) }}"
                                        class="text-primary-600 underline"
                                    >
                                        Edit Tenant
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">Tenant tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 dark:border-white/10">
                {{ $this->tenantRows->links() }}
            </div>
        </section>
    </div>
</x-filament-panels::page>

