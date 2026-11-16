<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Operations Portal') - Apex Logistics ERP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <div class="flex h-screen overflow-hidden bg-slate-100">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 transition-all duration-300 no-print">
            <!-- Brand Logo -->
            <div class="h-16 flex items-center px-6 bg-slate-950 gap-3 border-b border-slate-800">
                <div class="h-9 w-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-xl shadow">
                    ▲
                </div>
                <div>
                    <h1 class="text-white font-bold tracking-tight text-base leading-none">Apex Logistics</h1>
                    <span class="text-xs text-blue-400 font-medium">Enterprise ERP</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <div class="px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Operational Hub</div>
                
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Master Dashboard</span>
                </a>

                <a href="{{ route('shipments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('shipments.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>Shipments & AWBs</span>
                </a>

                <a href="{{ route('pickups.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('pickups.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    <span>Pickup Operations</span>
                </a>

                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Linehaul & Sorting</div>

                <a href="{{ route('hubs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('hubs.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Hubs & Inbound Scan</span>
                </a>

                <a href="{{ route('manifests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('manifests.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <span>Manifest & Dispatch</span>
                </a>

                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Last-Mile & Financials</div>

                <a href="{{ route('delivery.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('delivery.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Delivery Run Sheets (DRS)</span>
                </a>

                <a href="{{ route('agent.portal') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('agent.portal') ? 'bg-emerald-600 text-white shadow' : 'text-emerald-400 hover:bg-slate-800 hover:text-emerald-300' }}">
                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                    <span>Rider Mobile App</span>
                </a>

                <a href="{{ route('cod.collections') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('cod.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>COD & Settlements</span>
                </a>

                <a href="{{ route('rto.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('rto.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Reverse & RTO</span>
                </a>

                <a href="{{ route('billing.invoices') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('billing.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Invoicing & Ledger</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('customers.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Customers & Merchants</span>
                </a>

                <a href="{{ route('merchant.portal') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('merchant.portal') ? 'bg-indigo-600 text-white shadow' : 'text-indigo-400 hover:bg-slate-800 hover:text-indigo-300' }}">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Merchant Portal</span>
                </a>
            </div>

            <!-- User Footer Profile -->
            <div class="p-3 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3 truncate">
                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white font-bold flex items-center justify-center text-xs">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="truncate text-xs">
                        <div class="font-semibold text-white truncate">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role ?? 'Staff') }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top App Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-10 no-print">
                <div class="flex items-center gap-4">
                    <!-- Quick AWB Search -->
                    <form action="{{ route('tracking.public') }}" method="GET" class="relative w-80">
                        <input type="text" name="awb" placeholder="Track shipment by AWB..." class="w-full pl-9 pr-4 py-1.5 text-sm bg-slate-100 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Book Shipment</span>
                    </a>
                    <a href="{{ route('tracking.public') }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium transition">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Public Tracking</span>
                    </a>
                </div>
            </header>

            <!-- Alerts / Toast Messages -->
            <div class="px-6 pt-4 no-print">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                            <span>{{ session('warning') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>
