<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Operations Portal') - Webotapp Logistics ERP</title>
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
        /* Mobile touch scroll smoothing */
        .touch-scroll, .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    @php
        $user = auth()->user();
        $role = $user->role ?? 'staff';
        $isAdmin = in_array($role, ['admin', 'super_admin']);
        $isOps = in_array($role, ['operations', 'hub_manager', 'branch_manager']);
        $isRider = $role === 'delivery_agent';
        $isAccountant = $role === 'accountant';
        $isCustomer = in_array($role, ['customer', 'merchant']);
    @endphp

    <div class="flex h-screen overflow-hidden bg-slate-100" x-data="{ mobileMenuOpen: false }">
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-40 md:hidden" 
             @click="mobileMenuOpen = false" 
             x-cloak>
        </div>

        <!-- Sidebar Navigation (Desktop Fixed, Mobile Slide-out Drawer) -->
        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 md:w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out md:static md:translate-x-0 no-print shadow-2xl md:shadow-none">
            <!-- Brand Logo & Mobile Close -->
            <div class="h-16 flex items-center justify-between px-5 sm:px-6 bg-slate-950 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-xl shadow">
                        ▲
                    </div>
                    <div>
                        <h1 class="text-white font-bold tracking-tight text-base leading-none">Webotapp ERP</h1>
                        <span class="text-xs text-blue-400 font-medium">Logistics & Supply Chain</span>
                    </div>
                </div>
                <!-- Close Button on Mobile Drawer -->
                <button type="button" 
                        @click="mobileMenuOpen = false" 
                        class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none"
                        aria-label="Close sidebar">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation Links: Filtered strictly by User Role -->
            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 touch-scroll">
                @if($isAdmin || $isOps || $isAccountant)
                <div class="px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Dashboard & Core</div>
                
                <a href="{{ route('dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>
                        @if($isAccountant) Finance Dashboard
                        @elseif($isOps) Operations Dashboard
                        @else Master Dashboard
                        @endif
                    </span>
                </a>
                @endif

                @if($isCustomer)
                <!-- Merchant Portal Menu -->
                <div class="px-3 pb-2 text-[11px] font-semibold text-indigo-400 uppercase tracking-wider">Merchant Portal</div>
                
                <a href="{{ route('merchant.portal') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('merchant.portal') ? 'bg-indigo-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="store" class="w-4 h-4 text-indigo-400"></i>
                    <span>Portal Overview</span>
                </a>

                <a href="{{ route('shipments.create') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('shipments.create') ? 'bg-indigo-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Book Consignment</span>
                </a>

                <a href="{{ route('shipments.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('shipments.index', 'shipments.show') ? 'bg-indigo-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>My Shipments</span>
                </a>

                <a href="{{ route('cod.settlements') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('cod.settlements') ? 'bg-indigo-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>COD Remittances</span>
                </a>

                <a href="{{ route('billing.invoices') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('billing.*') ? 'bg-indigo-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Invoices & Billing</span>
                </a>
                @endif

                @if($isRider)
                <!-- Delivery Rider Menu -->
                <div class="px-3 pb-2 text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">Field Delivery App</div>

                <a href="{{ route('agent.portal') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('agent.portal') ? 'bg-emerald-600 text-white shadow' : 'text-emerald-400 hover:bg-slate-800 hover:text-emerald-300' }}">
                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                    <span>Active Deliveries</span>
                </a>

                <a href="{{ route('delivery.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('delivery.*') ? 'bg-emerald-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Delivery Run Sheets</span>
                </a>

                <a href="{{ route('cod.collections') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('cod.collections') ? 'bg-emerald-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>My Cash In Hand</span>
                </a>
                @endif

                @if($isAdmin || $isOps || $isAccountant)
                <a href="{{ route('shipments.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('shipments.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>Shipments & AWBs</span>
                </a>
                @endif

                @if($isAdmin || $isOps)
                <a href="{{ route('pickups.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('pickups.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    <span>Pickup Operations</span>
                </a>

                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Linehaul & Sorting</div>

                <a href="{{ route('hubs.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('hubs.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Hubs & Inbound Scan</span>
                </a>

                <a href="{{ route('manifests.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('manifests.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <span>Manifest & Linehaul</span>
                </a>

                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Last-Mile Execution</div>

                <a href="{{ route('delivery.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('delivery.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Delivery Run Sheets (DRS)</span>
                </a>

                <a href="{{ route('rto.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('rto.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Reverse & RTO</span>
                </a>
                @endif

                @if($isAdmin || $isAccountant)
                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Financials & Accounts</div>

                <a href="{{ route('cod.collections') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('cod.collections') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>COD Collections</span>
                </a>

                <a href="{{ route('cod.settlements') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('cod.settlements') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Merchant Settlements</span>
                </a>

                <a href="{{ route('billing.invoices') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('billing.invoices', 'billing.invoice_detail') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Commercial Invoices</span>
                </a>

                <a href="{{ route('billing.expenses') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('billing.expenses') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Fleet & Branch Expenses</span>
                </a>

                <a href="{{ route('billing.ledger') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('billing.ledger') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>General Ledger & COA</span>
                </a>
                @endif

                @if($isAdmin)
                <div class="pt-4 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Administration</div>

                <a href="{{ route('customers.index') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('customers.*') ? 'bg-blue-600 text-white shadow' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Customers & Merchants</span>
                </a>

                <a href="{{ route('agent.portal') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('agent.portal') ? 'bg-emerald-600 text-white shadow' : 'text-emerald-400 hover:bg-slate-800 hover:text-emerald-300' }}">
                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                    <span>Rider App Preview</span>
                </a>

                <a href="{{ route('merchant.portal') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('merchant.portal') ? 'bg-indigo-600 text-white shadow' : 'text-indigo-400 hover:bg-slate-800 hover:text-indigo-300' }}">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Merchant Portal Preview</span>
                </a>
                @endif
            </div>

            <!-- User Footer Profile -->
            <div class="p-3 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3 truncate">
                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white font-bold flex items-center justify-center text-xs">
                        {{ substr($user->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="truncate text-xs">
                        <div class="font-semibold text-white truncate">{{ $user->name ?? 'User' }}</div>
                        <div class="text-blue-400 font-semibold capitalize">{{ str_replace('_', ' ', $role) }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top App Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-3 sm:px-6 z-10 no-print">
                <div class="flex items-center gap-2 sm:gap-4 flex-1 min-w-0">
                    <!-- Mobile Hamburger Button -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg md:hidden focus:outline-none focus:ring-2 focus:ring-blue-500 flex-shrink-0"
                            aria-label="Toggle navigation menu">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    <!-- Quick AWB Search -->
                    <form action="{{ route('tracking.public') }}" method="GET" class="relative flex-1 max-w-[200px] sm:max-w-xs md:max-w-sm">
                        <input type="text" name="awb" placeholder="Track AWB..." class="w-full pl-8 sm:pl-9 pr-3 py-1.5 text-xs sm:text-sm bg-slate-100 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <i data-lucide="search" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-slate-400 absolute left-2.5 sm:left-3 top-2.5"></i>
                    </form>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0 ml-2">
                    @if(! $isRider)
                    <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Book Shipment</span>
                        <span class="sm:hidden">Book</span>
                    </a>
                    @endif
                    <a href="{{ route('tracking.public') }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-medium transition" title="Public Tracking">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span class="hidden md:inline">Public Tracking</span>
                    </a>
                </div>
            </header>

            <!-- Alerts / Toast Messages -->
            <div class="px-3 sm:px-6 pt-3 sm:pt-4 no-print">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg text-xs sm:text-sm flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg text-xs sm:text-sm flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-3.5 py-2.5 sm:px-4 sm:py-3 rounded-lg text-xs sm:text-sm flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
                            <span>{{ session('warning') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page Body: With bottom padding for mobile navigation bar -->
            <main class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 pb-20 md:pb-6 touch-scroll">
                @yield('content')
            </main>

            <!-- Mobile Bottom Quick Navigation Bar (Sticky on Smartphones) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 h-14 bg-white/95 backdrop-blur-md border-t border-slate-200 z-30 flex items-center justify-around px-2 no-print shadow-lg">
                @if($isRider)
                    <a href="{{ route('agent.portal') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('agent.portal') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="smartphone" class="w-5 h-5 mb-0.5"></i>
                        <span>Deliveries</span>
                    </a>
                    <a href="{{ route('delivery.index') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('delivery.*') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="map-pin" class="w-5 h-5 mb-0.5"></i>
                        <span>Run Sheets</span>
                    </a>
                    <a href="{{ route('cod.collections') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('cod.collections') ? 'text-emerald-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="banknote" class="w-5 h-5 mb-0.5"></i>
                        <span>Cash</span>
                    </a>
                    <button type="button" @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold text-slate-500 hover:text-slate-900">
                        <i data-lucide="menu" class="w-5 h-5 mb-0.5"></i>
                        <span>More</span>
                    </button>
                @elseif($isCustomer)
                    <a href="{{ route('merchant.portal') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('merchant.portal') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="store" class="w-5 h-5 mb-0.5"></i>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('shipments.create') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('shipments.create') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="plus-circle" class="w-5 h-5 mb-0.5"></i>
                        <span>Book</span>
                    </a>
                    <a href="{{ route('shipments.index') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('shipments.index') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="package" class="w-5 h-5 mb-0.5"></i>
                        <span>Shipments</span>
                    </a>
                    <a href="{{ route('billing.invoices') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('billing.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="receipt" class="w-5 h-5 mb-0.5"></i>
                        <span>Invoices</span>
                    </a>
                    <button type="button" @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold text-slate-500 hover:text-slate-900">
                        <i data-lucide="menu" class="w-5 h-5 mb-0.5"></i>
                        <span>More</span>
                    </button>
                @else
                    <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 mb-0.5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('shipments.index') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('shipments.*') ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="package" class="w-5 h-5 mb-0.5"></i>
                        <span>Shipments</span>
                    </a>
                    @if($isAdmin || $isOps)
                    <a href="{{ route('delivery.index') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('delivery.*') ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="map-pin" class="w-5 h-5 mb-0.5"></i>
                        <span>DRS</span>
                    </a>
                    <a href="{{ route('manifests.index') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('manifests.*') ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="truck" class="w-5 h-5 mb-0.5"></i>
                        <span>Linehaul</span>
                    </a>
                    @else
                    <a href="{{ route('billing.invoices') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('billing.*') ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="receipt" class="w-5 h-5 mb-0.5"></i>
                        <span>Invoices</span>
                    </a>
                    <a href="{{ route('cod.collections') }}" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold {{ request()->routeIs('cod.*') ? 'text-blue-600' : 'text-slate-500 hover:text-slate-900' }}">
                        <i data-lucide="banknote" class="w-5 h-5 mb-0.5"></i>
                        <span>COD</span>
                    </a>
                    @endif
                    <button type="button" @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center py-1 px-3 text-[10px] font-semibold text-slate-500 hover:text-slate-900">
                        <i data-lucide="menu" class="w-5 h-5 mb-0.5"></i>
                        <span>More</span>
                    </button>
                @endif
            </nav>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
        window.addEventListener('load', function() {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>