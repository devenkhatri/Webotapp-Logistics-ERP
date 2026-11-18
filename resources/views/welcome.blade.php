<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webotapp Logistics ERP - Enterprise Courier & Supply Chain Solutions</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">
    <!-- Navigation Bar -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-xl shadow">▲</div>
                <div>
                    <span class="font-extrabold text-slate-900 tracking-tight text-lg">Webotapp Logistics</span>
                    <span class="hidden sm:inline-block ml-2 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-blue-50 text-blue-700">Enterprise ERP</span>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 text-xs font-semibold">
                <a href="#tracking" class="hidden sm:inline-block text-slate-600 hover:text-blue-600 transition">Track Consignment</a>
                <a href="#services" class="hidden sm:inline-block text-slate-600 hover:text-blue-600 transition">Services</a>
                <a href="{{ route('login') }}" class="px-3.5 sm:px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition">
                    Sign In / Portal
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section with Live Tracking Bar -->
    <section class="relative bg-gradient-to-b from-slate-950 via-slate-900 to-slate-900 text-white py-20 px-6 overflow-hidden">
        <div class="max-w-4xl mx-auto text-center space-y-6 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-900/60 border border-blue-700/60 text-blue-300 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Next-Gen Logistics, Linehaul & COD Remittance Platform
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                Precision Courier & <br class="hidden sm:block"><span class="text-blue-500">Express Supply Chain</span> ERP
            </h1>

            <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto font-normal">
                End-to-end commercial logistics operations — from automated consignment booking and AWB generation to hub sorting, manifest linehaul, OTP delivery proof, and automated COD settlements.
            </p>

            <!-- Tracking Bar -->
            <div id="tracking" class="max-w-xl mx-auto pt-4">
                <form action="{{ route('tracking.public') }}" method="GET" class="p-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="awb" placeholder="Enter Tracking No. (e.g. CX100001, CX100004)" required class="w-full pl-10 pr-4 py-3 bg-white text-slate-900 rounded-xl font-mono text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-4"></i>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                        <span>Track Consignment</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <div class="flex items-center justify-center gap-4 text-xs text-slate-400 mt-3">
                    <span>Try sample AWBs:</span>
                    <a href="{{ route('tracking.public', 'CX100001') }}" class="underline hover:text-white font-mono">CX100001</a>
                    <a href="{{ route('tracking.public', 'CX100004') }}" class="underline hover:text-white font-mono">CX100004</a>
                    <a href="{{ route('tracking.public', 'CX100005') }}" class="underline hover:text-white font-mono">CX100005</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Metrics Stats Strip -->
    <section class="bg-white border-y border-slate-200 py-8 px-6">
        <div class="max-w-6xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-3xl font-black text-blue-600">{{ $stats['total_shipments'] ?? '2,500+' }}</div>
                <div class="text-xs text-slate-500 font-semibold mt-1">Consignments Handled</div>
            </div>
            <div>
                <div class="text-3xl font-black text-emerald-600">{{ $stats['on_time_rate'] ?? '99.4%' }}</div>
                <div class="text-xs text-slate-500 font-semibold mt-1">On-Time SLA Delivery</div>
            </div>
            <div>
                <div class="text-3xl font-black text-slate-900">{{ $stats['cities_covered'] ?? '250+' }}</div>
                <div class="text-xs text-slate-500 font-semibold mt-1">Distribution Hubs & Pockets</div>
            </div>
            <div>
                <div class="text-3xl font-black text-indigo-600">100%</div>
                <div class="text-xs text-slate-500 font-semibold mt-1">COD Reconciliation Accuracy</div>
            </div>
        </div>
    </section>

    <!-- Core ERP Capabilities Section -->
    <section id="services" class="py-16 px-6 max-w-6xl mx-auto space-y-12">
        <div class="text-center space-y-3">
            <h2 class="text-xs font-extrabold uppercase tracking-widest text-blue-600">Industrial Logistics Capabilities</h2>
            <h3 class="text-3xl font-extrabold text-slate-900">Complete Operational Lifecycle</h3>
            <p class="text-sm text-slate-500 max-w-2xl mx-auto">Built from the ground up for courier companies, 3PL logistics providers, e-commerce fulfillment networks, and freight forwarders.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 1 -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-base">Consignment & AWB Engine</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Automated unique AWB generation, Code-128 barcode encoding, volumetric weight divisor (5000), itemized tariff slabs, and 4x6 thermal shipping label printing.
                </p>
            </div>

            <!-- 2 -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <i data-lucide="scan" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-base">Hub Inbound & Bagging</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    High-speed barcode scanner terminal for arrival scans at sorting centers, routing bin allocation, security seal tag bag consolidation, and inventory movement logs.
                </p>
            </div>

            <!-- 3 -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-base">Linehaul Manifest & Dispatch</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Inter-hub freight transfer manifests with vehicle fleet assignment, driver dispatch, weight balance manifests, and automated arrival receiving at destination hubs.
                </p>
            </div>

            <!-- 4 -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-base">Last-Mile DRS & e-POD</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Mobile rider application, Delivery Run Sheets (DRS), customer calling, delivery OTP verification, electronic signature capture, and reason-coded failure attempts.
                </p>
            </div>

            <!-- 5 -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-base">COD Reconciliation & Escrow</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Rider cash deposit verification at branches, automated merchant settlement statement generation with shipping fee deduction, and bank UTR reference recording.
                </p>
            </div>

            <!-- 6 -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-base">RTO & Reverse Logistics</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Automated Return to Origin (RTO) triggering after 3 failed delivery attempts or customer refusal, reverse AWB generation, return transit tracking, and merchant handover.
                </p>
            </div>
        </div>
    </section>

    <!-- 1-Click Role Testing CTA Strip -->
    <section class="bg-slate-900 text-white py-12 px-6">
        <div class="max-w-4xl mx-auto text-center space-y-6">
            <h3 class="text-2xl font-bold">Evaluate Live Logistics System Roles</h3>
            <p class="text-xs text-slate-400">Click below to immediately log in with any operational role in 1 click without needing to type passwords:</p>

            <div class="flex flex-wrap justify-center gap-2 text-xs">
                <a href="{{ route('quick.login', 'admin') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold transition">
                    Administrator
                </a>
                <a href="{{ route('quick.login', 'operations') }}" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-semibold transition">
                    Operations Hub Manager
                </a>
                <a href="{{ route('quick.login', 'rider') }}" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition">
                    Delivery Rider / Agent (Mobile)
                </a>
                <a href="{{ route('quick.login', 'accountant') }}" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-semibold transition">
                    Finance & Accounts
                </a>
                <a href="{{ route('quick.login', 'merchant') }}" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition">
                    Merchant Portal (FashionNova)
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-10 px-6 border-t border-slate-800 text-xs">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
            <div>
                <strong class="text-white text-sm">Apex Logistics Express Inc.</strong>
                <p class="text-slate-500 mt-1">Enterprise Courier & Supply Chain Management ERP Platform</p>
            </div>
            <div class="text-slate-500">
                © {{ date('Y') }} Apex Logistics Express. All rights reserved.
            </div>
        </div>
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>
