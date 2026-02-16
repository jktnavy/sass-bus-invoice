<x-filament-panels::page>
    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-cyan-500 via-sky-500 to-blue-600 p-6 text-white shadow-lg dark:border-white/10">
            <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/20 blur-2xl"></div>
            <div class="absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-cyan-300/30 blur-3xl"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">Bus Invoice SaaS</p>
                <h1 class="mt-2 text-2xl font-bold">Panduan Penggunaan Sistem</h1>
                <p class="mt-2 max-w-3xl text-sm text-cyan-50">
                    Alur sistem dari login sampai pelaporan: kelola user, buat quotation, ubah menjadi invoice, proses pembayaran, lalu monitor dokumen dan audit.
                </p>
                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-white/20 px-3 py-1">Multi-tenant secure</span>
                    <span class="rounded-full bg-white/20 px-3 py-1">Quotation to Invoice</span>
                    <span class="rounded-full bg-white/20 px-3 py-1">Payment Allocation</span>
                    <span class="rounded-full bg-white/20 px-3 py-1">PDF & Audit Trail</span>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('filament.admin.resources.users.index') }}"
               class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah 1</p>
                <h3 class="mt-1 text-base font-semibold">Kelola User</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Tambah user sesuai role: Admin, Sales, Finance.</p>
                <p class="mt-3 text-sm font-medium text-primary-600 group-hover:underline">Buka menu Users</p>
            </a>

            <a href="{{ route('filament.admin.resources.quotations.index') }}"
               class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah 2</p>
                <h3 class="mt-1 text-base font-semibold">Buat Quotation</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Isi header, tambah item, dan generate PDF quotation.</p>
                <p class="mt-3 text-sm font-medium text-primary-600 group-hover:underline">Buka menu Quotations</p>
            </a>

            <a href="{{ route('filament.admin.resources.invoices.index') }}"
               class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Langkah 3</p>
                <h3 class="mt-1 text-base font-semibold">Invoice & Payment</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Convert quotation, post payment, alokasikan ke invoice.</p>
                <p class="mt-3 text-sm font-medium text-primary-600 group-hover:underline">Buka menu Invoices</p>
            </a>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">A. Cara Membuat User</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-cyan-100 text-xs font-bold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">1</span>
                        <span>Login sebagai <strong>Admin</strong>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-cyan-100 text-xs font-bold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">2</span>
                        <span>Buka menu <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.users.index') }}">Users</a>, lalu klik <strong>Create</strong>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-cyan-100 text-xs font-bold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">3</span>
                        <span>Isi nama, email, role (<code>admin</code>/<code>sales</code>/<code>finance</code>), dan password.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-cyan-100 text-xs font-bold text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">4</span>
                        <span>Klik save. User otomatis masuk tenant yang sama.</span>
                    </li>
                </ol>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">B. Cara Membuat Quotation</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">1</span>
                        <span>Pastikan master data tersedia: <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.customers.index') }}">Customers</a>, <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.items.index') }}">Items</a>, <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.taxes.index') }}">Taxes</a>, <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.number-sequences.index') }}">Number Sequences</a>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">2</span>
                        <span>Buka <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.quotations.index') }}">Quotations</a> -> <strong>Create</strong> -> isi header.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">3</span>
                        <span>Setelah save, tambahkan item di relation <strong>Quotation Items</strong>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">4</span>
                        <span>Total otomatis dihitung. Klik <strong>Generate PDF</strong> bila diperlukan.</span>
                    </li>
                </ol>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">C. Cara Membuat Invoice</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">1</span>
                        <span>Dari quotation: klik <strong>Convert to Invoice</strong> lalu isi due date.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">2</span>
                        <span>Atau manual dari <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.invoices.index') }}">Invoices</a> -> <strong>Create</strong>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">3</span>
                        <span>Gunakan <strong>Mark Sent</strong> saat invoice terkirim.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">4</span>
                        <span>Gunakan <strong>Generate PDF</strong> untuk arsip dokumen.</span>
                    </li>
                </ol>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <h2 class="text-lg font-semibold">D. Workflow Login sampai Laporan</h2>
                <ol class="mt-4 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">1</span>
                        <span>Login ke panel admin.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">2</span>
                        <span>Siapkan master data tenant.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">3</span>
                        <span>Buat quotation lalu convert ke invoice.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">4</span>
                        <span>Buat payment draft -> post -> allocate ke invoice.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">5</span>
                        <span>Monitoring di <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.documents.index') }}">Documents</a> dan <a class="font-medium text-primary-600 underline" href="{{ route('filament.admin.resources.audit-logs.index') }}">Audit Logs</a>.</span>
                    </li>
                </ol>
            </div>
        </section>

        <section class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-950/40 dark:text-amber-200">
            <strong>Tenant Security:</strong> Semua halaman dan data diisolasi per tenant. User tenant A tidak dapat melihat data tenant B.
        </section>
    </div>
</x-filament-panels::page>
