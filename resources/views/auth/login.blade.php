<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Apex Logistics ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 rounded-xl bg-blue-600 items-center justify-center text-white font-bold text-2xl shadow-lg mb-3">
                ▲
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Apex Logistics ERP</h1>
            <p class="text-sm text-slate-400 mt-1">Enterprise Courier & Supply Chain Management</p>
        </div>

        <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-xl p-8">
            <h2 class="text-lg font-semibold text-white mb-6">Sign in to your account</h2>

            @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-rose-950 border border-rose-800 text-rose-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@courierx.com') }}" required class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Password</label>
                    <input type="password" name="password" value="password" required class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-blue-600 focus:ring-blue-500">
                        <span>Remember me</span>
                    </label>
                    <span class="text-slate-500">Password: <code class="text-slate-300">password</code></span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm shadow transition">
                    Sign In
                </button>
            </form>

            <!-- Quick Demo Logins -->
            <div class="mt-8 pt-6 border-t border-slate-700">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 text-center">Instant 1-Click Role Testing</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('quick.login', 'admin') }}" class="p-2 rounded bg-slate-900 hover:bg-slate-700 text-slate-300 text-center font-medium border border-slate-750 transition flex items-center justify-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> Admin
                    </a>
                    <a href="{{ route('quick.login', 'operations') }}" class="p-2 rounded bg-slate-900 hover:bg-slate-700 text-slate-300 text-center font-medium border border-slate-750 transition flex items-center justify-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span> Ops Manager
                    </a>
                    <a href="{{ route('quick.login', 'rider') }}" class="p-2 rounded bg-slate-900 hover:bg-slate-700 text-slate-300 text-center font-medium border border-slate-750 transition flex items-center justify-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Delivery Rider
                    </a>
                    <a href="{{ route('quick.login', 'accountant') }}" class="p-2 rounded bg-slate-900 hover:bg-slate-700 text-slate-300 text-center font-medium border border-slate-750 transition flex items-center justify-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Accountant
                    </a>
                    <a href="{{ route('quick.login', 'merchant') }}" class="col-span-2 p-2 rounded bg-slate-900 hover:bg-slate-700 text-slate-300 text-center font-medium border border-slate-750 transition flex items-center justify-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> E-Commerce Merchant Portal (FashionNova)
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-slate-300 transition">← Back to Public Website</a>
            <span class="mx-2">•</span>
            <a href="{{ route('tracking.public') }}" class="hover:text-slate-300 transition">Public Tracking</a>
        </div>
    </div>
</body>
</html>
