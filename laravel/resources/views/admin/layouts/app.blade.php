<!doctype html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Admin' }} · Be Better BSBL Admin</title>
    <link rel="stylesheet" href="{{ asset('build/admin.css') }}" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">
    <div class="min-h-full">
        {{-- Top navigation --}}
        <nav class="bg-brand-900 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-14 items-center justify-between">
                    <div class="flex items-center gap-6">
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-2 text-white font-bold tracking-wide text-sm">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L20.5 5.5V12.6C20.5 17.1 17.6 20.2 12 22C6.4 20.2 3.5 17.1 3.5 12.6V5.5L12 2Z" stroke="currentColor" stroke-width="1.6"/>
                                <path d="M10 7.2H13.2C15 7.2 16.2 8.1 16.2 9.6C16.2 10.6 15.6 11.3 14.7 11.6C15.8 11.9 16.6 12.8 16.6 14C16.6 15.8 15.2 16.8 13.2 16.8H10V7.2ZM11.8 11.1H13C13.9 11.1 14.4 10.6 14.4 9.9C14.4 9.2 13.9 8.8 13 8.8H11.8V11.1ZM11.8 15.2H13.1C14.1 15.2 14.7 14.7 14.7 13.9C14.7 13.1 14.1 12.6 13.1 12.6H11.8V15.2Z" fill="currentColor"/>
                            </svg>
                            <span>BB ADMIN</span>
                        </a>

                        <div class="hidden sm:flex items-center gap-1">
                            <a href="{{ route('admin.products.index') }}"
                               class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.products.*') ? 'bg-brand-800 text-white' : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                                Products
                            </a>
                            <a href="{{ route('admin.homepage-content.edit') }}"
                               class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.homepage-content.*') ? 'bg-brand-800 text-white' : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                                Homepage
                            </a>
                            <a href="{{ route('admin.pages.index') }}"
                               class="px-3 py-1.5 rounded-md text-sm font-medium {{ request()->routeIs('admin.pages.*') ? 'bg-brand-800 text-white' : 'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                                Pages
                            </a>
                            <a href="{{ route('home') }}" target="_blank"
                               class="px-3 py-1.5 rounded-md text-sm font-medium text-brand-200 hover:bg-brand-800 hover:text-white">
                                View Store ↗
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="hidden sm:inline text-brand-300 text-xs">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-brand-200 hover:text-white">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        {{-- Flash messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-md bg-green-50 p-3 flex items-center justify-between border border-green-200">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show"
                 class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-md bg-red-50 p-3 flex items-center justify-between border border-red-200">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                    <button @click="show = false" class="text-red-600 hover:text-red-800">&times;</button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-md bg-red-50 p-3 border border-red-200">
                    <ul class="list-disc list-inside text-sm text-red-800">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Page content --}}
        <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

