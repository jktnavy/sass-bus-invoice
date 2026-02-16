<x-filament-panels::page>
    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Quotation</p>
                <p class="mt-2 text-2xl font-bold">{{ number_format($this->totalQuotationCount) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-950/40">
                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Sudah Ada Invoice</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($this->totalInvoicedCount) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-500/30 dark:bg-amber-950/40">
                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Belum Ada Invoice</p>
                <p class="mt-2 text-2xl font-bold text-amber-700 dark:text-amber-300">{{ number_format($this->totalUninvoicedCount) }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="grid gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Status</label>
                    <select wire:model.live="statusFilter" class="w-full rounded-lg border-slate-300 text-sm dark:border-white/20 dark:bg-gray-800">
                        <option value="uninvoiced">Belum Invoice</option>
                        <option value="all">Semua</option>
                        <option value="invoiced">Sudah Invoice</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date From</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full rounded-lg border-slate-300 text-sm dark:border-white/20 dark:bg-gray-800" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300">Date To</label>
                    <input type="date" wire:model.live="dateTo" class="w-full rounded-lg border-slate-300 text-sm dark:border-white/20 dark:bg-gray-800" />
                </div>
                <div class="flex items-end">
                    <button type="button" wire:click="$set('dateFrom', null); $set('dateTo', null); $set('statusFilter', 'uninvoiced')" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:border-white/20 dark:hover:bg-white/5">
                        Reset Filter
                    </button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-white/10">
                    <thead class="bg-slate-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Quotation</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold">Customer</th>
                            <th class="px-4 py-3 text-right font-semibold">Grand Total</th>
                            <th class="px-4 py-3 text-left font-semibold">Invoice</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        @forelse ($this->rows as $row)
                            @php
                                $invoice = $row->invoices->first();
                                $isInvoiced = (bool) $invoice;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium">
                                    <a href="{{ route('filament.admin.resources.quotations.edit', ['record' => $row->id]) }}" class="text-primary-600 underline">
                                        {{ $row->number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">{{ optional($row->date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">{{ $row->customer?->name }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format((float) $row->grand_total, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if ($invoice)
                                        <a href="{{ route('filament.admin.resources.invoices.edit', ['record' => $invoice->id]) }}" class="text-primary-600 underline">
                                            {{ $invoice->number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($isInvoiced)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Sudah Invoice</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Belum Invoice</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada data quotation.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-4 py-3 dark:border-white/10">
                {{ $this->rows->links() }}
            </div>
        </section>
    </div>
</x-filament-panels::page>
