<x-filament-panels::page>
    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-cyan-500 via-sky-500 to-blue-600 p-6 text-white shadow-lg dark:border-white/10">
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/20 blur-2xl"></div>
            <div class="absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-cyan-300/30 blur-3xl"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">Bus Invoice SaaS</p>
                <h1 class="mt-2 text-2xl font-bold">Penggunaan Sistem</h1>
                <p class="mt-2 max-w-3xl text-sm text-cyan-50">
                    Alur utama: kelola master data, buat quotation, buat invoice, proses payment + allocation, lalu monitoring laporan.
                </p>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('filament.admin.resources.tenants.index') }}"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah 1</p>
                <h3 class="mt-1 text-base font-semibold">Informasi Tenant</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Lengkapi profil perusahaan, logo, cap, tanda tangan, dan rekening.</p>
                <p class="mt-3 text-sm font-medium text-primary-600 group-hover:underline">Buka Informasi Tenant</p>
            </a>

            <a href="{{ route('filament.admin.resources.quotations.index') }}"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah 2</p>
                <h3 class="mt-1 text-base font-semibold">Quotation</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Buat quotation, isi item, lalu preview/download PDF.</p>
                <p class="mt-3 text-sm font-medium text-primary-600 group-hover:underline">Buka Quotations</p>
            </a>

            <a href="{{ route('filament.admin.resources.payments.index') }}"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah 3</p>
                <h3 class="mt-1 text-base font-semibold">Invoice & Payment</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Convert ke invoice, post payment, lalu alokasikan pembayaran.</p>
                <p class="mt-3 text-sm font-medium text-primary-600 group-hover:underline">Buka Payments</p>
            </a>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">A. User & Tenant</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li>Superadmin bisa CRUD tenant dan user lintas tenant.</li>
                    <li>Admin tenant hanya bisa edit tenant miliknya (tanpa create/delete tenant).</li>
                    <li>Buka <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.users.index') }}">Users</a> untuk buat user sesuai role.</li>
                </ol>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">B. Quotation</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li>Siapkan master data: <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.customers.index') }}">Customers</a>, <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.items.index') }}">Items</a>, <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.taxes.index') }}">Taxes</a>.</li>
                    <li>Buka <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.quotations.index') }}">Quotations</a> lalu create.</li>
                    <li>Isi item quotation, total dihitung otomatis.</li>
                    <li>Gunakan aksi Preview/Download PDF untuk surat quotation.</li>
                </ol>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">C. Invoice & Payment</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li>Dari quotation gunakan create invoice draft, atau create invoice manual.</li>
                    <li>Invoice bisa preview/download PDF, dan kwitansi jika status lunas.</li>
                    <li>Buat payment dalam status draft, lalu klik Post.</li>
                    <li>Di tab Allocations, pilih invoice dan nominal alokasi.</li>
                </ol>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">D. Monitoring & Laporan</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li>Gunakan report <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.pages.laporan-quotation-vs-invoice') }}">Quotation vs Invoice</a> untuk melihat quotation yang belum jadi invoice.</li>
                    <li>Gunakan <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.audit-logs.index') }}">Audit Logs</a> untuk jejak aktivitas.</li>
                    @if (auth()->user()?->role === 'superadmin')
                        <li>Untuk pemilik SaaS, gunakan <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.pages.saas-overview') }}">SaaS Monitor</a> untuk pantau tenant, user, quotation, invoice, dan payment.</li>
                    @endif
                </ol>
            </div>
        </section>

        <section class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
            <strong>Tenant Security:</strong> Data tenant terisolasi. User tenant A tidak bisa melihat data tenant B.
        </section>
    </div>
</x-filament-panels::page>
