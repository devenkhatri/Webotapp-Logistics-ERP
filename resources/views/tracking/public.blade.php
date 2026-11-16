<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Consignment Tracking - Apex Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Navbar -->
    <header class="bg-white border-b border-slate-200 py-4 px-6 shadow-sm">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow">▲</div>
                <span class="font-extrabold text-slate-900 tracking-tight text-lg">Apex Logistics</span>
            </a>

            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="{{ route('home') }}" class="text-slate-600 hover:text-blue-600 transition">Home</a>
                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">Operations Login</a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto w-full px-4 py-10 flex-1 space-y-8">
        <!-- Search Bar -->
        <div class="text-center space-y-3">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Live Consignment Tracking</h1>
            <p class="text-sm text-slate-500">Enter your Air Waybill (AWB) or Merchant Order Reference Number</p>

            <form action="{{ route('tracking.public') }}" method="GET" class="max-w-lg mx-auto flex gap-2 pt-2">
                <div class="relative flex-1">
                    <input type="text" name="awb" value="{{ $searchAwb }}" placeholder="e.g. CX100001, CX100004..." required class="w-full pl-10 pr-4 py-3 bg-white border border-slate-300 rounded-xl font-mono text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-sm transition">
                    <i data-lucide="package" class="w-5 h-5 text-slate-400 absolute left-3 top-3.5"></i>
                </div>
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Track</span>
                </button>
            </form>
        </div>

        @if($shipment)
        <!-- Tracking Result Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden space-y-6 p-6 md:p-8">
            <!-- Header Info -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-6 gap-4">
                <div>
                    <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Air Waybill Number</span>
                    <h2 class="text-2xl font-mono font-extrabold text-blue-600 mt-0.5">{{ $shipment->awb_number }}</h2>
                    @if($shipment->reference_number)
                        <div class="text-xs text-slate-500 mt-0.5">Order Ref: <strong>{{ $shipment->reference_number }}</strong></div>
                    @endif
                </div>

                <div class="text-right sm:text-right">
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-extrabold border {{ $shipment->status_badge }}">
                        {{ ucwords(str_replace('_', ' ', $shipment->status)) }}
                    </span>
                    <div class="text-xs text-slate-500 mt-1">Est. Delivery: <strong>{{ $shipment->estimated_delivery_at?->format('d M Y') ?? 'N/A' }}</strong></div>
                </div>
            </div>

            <!-- Stepper Progress Bar -->
            @php
                $stages = [
                    'booked' => 'Booked',
                    'picked_up' => 'Picked Up',
                    'in_transit' => 'In Transit',
                    'out_for_delivery' => 'Out for Delivery',
                    'delivered' => 'Delivered'
                ];
                $statusOrder = ['booked', 'picked_up', 'received_at_hub', 'sorted', 'bagged', 'in_transit', 'out_for_delivery', 'delivered'];
                $currentIdx = array_search($shipment->status, $statusOrder);
                if ($currentIdx === false) $currentIdx = 1;
            @endphp
            <div class="py-4">
                <div class="grid grid-cols-5 text-center relative">
                    <div class="absolute top-4 left-6 right-6 h-0.5 bg-slate-200 -z-0"></div>

                    @foreach(['booked' => 0, 'picked_up' => 1, 'in_transit' => 5, 'out_for_delivery' => 6, 'delivered' => 7] as $st => $ord)
                    @php
                        $isPassed = $currentIdx >= $ord;
                        $isCurrent = $shipment->status === $st || ($st === 'in_transit' && in_transit_status($shipment->status));
                    @endphp
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition {{ $isPassed ? 'bg-blue-600 text-white shadow ring-4 ring-blue-50' : 'bg-slate-200 text-slate-500' }}">
                            @if($isPassed)
                                <i data-lucide="check" class="w-4 h-4"></i>
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </div>
                        <span class="text-[11px] font-semibold mt-2 {{ $isPassed ? 'text-slate-900' : 'text-slate-400' }}">
                            {{ $stages[$st] ?? $st }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Origin & Destination Summary Box -->
            <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                <div>
                    <span class="text-slate-400 block uppercase font-bold text-[10px]">Origin</span>
                    <strong class="text-slate-800 text-sm">{{ $shipment->sender_city }}</strong>
                    <div class="text-slate-500">{{ $shipment->sender_postal_code }}</div>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block uppercase font-bold text-[10px]">Destination</span>
                    <strong class="text-slate-800 text-sm">{{ $shipment->receiver_city }}</strong>
                    <div class="text-slate-600 font-medium">{{ $shipment->receiver_name }}</div>
                    <div class="text-slate-500">{{ $shipment->receiver_postal_code }}</div>
                </div>
            </div>

            <!-- Milestone Events Timeline -->
            <div class="space-y-4 pt-2">
                <h3 class="font-bold text-slate-900 text-sm">Detailed Transit History</h3>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @foreach($shipment->trackingEvents as $idx => $ev)
                    <div class="relative">
                        <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full {{ $idx === 0 ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-slate-400' }}"></div>

                        <div class="text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-sm">{{ $ev->status_title }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">{{ $ev->event_time->format('d M Y, h:i A') }}</span>
                            </div>
                            <span class="text-slate-500 text-xs">{{ $ev->location }}</span>
                            @if($ev->description)
                                <p class="text-slate-600 mt-1 bg-slate-50 p-2 rounded border border-slate-100 text-xs">{{ $ev->description }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @elseif($searchAwb)
        <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center shadow-sm space-y-3">
            <i data-lucide="package-search" class="w-10 h-10 text-slate-400 mx-auto"></i>
            <h3 class="font-bold text-slate-800 text-base">No shipment found with AWB "{{ $searchAwb }}"</h3>
            <p class="text-xs text-slate-500">Please verify your tracking number and try again, or contact customer care.</p>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        © {{ date('Y') }} Apex Logistics Express Inc. All rights reserved. • Support Hotline: +1 (800) 555-0199
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>
@php
function in_transit_status($status) {
    return in_array($status, ['in_transit', 'received_at_hub', 'sorted', 'bagged', 'dispatched']);
}
@endphp
