<!doctype html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign In · Be Better BSBL Admin</title>
    <link rel="stylesheet" href="{{ asset('build/admin.css') }}" />
</head>
<body class="h-full flex items-center justify-center">
    <div class="w-full max-w-sm px-6">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-brand-900 text-white mb-4">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L20.5 5.5V12.6C20.5 17.1 17.6 20.2 12 22C6.4 20.2 3.5 17.1 3.5 12.6V5.5L12 2Z" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M10 7.2H13.2C15 7.2 16.2 8.1 16.2 9.6C16.2 10.6 15.6 11.3 14.7 11.6C15.8 11.9 16.6 12.8 16.6 14C16.6 15.8 15.2 16.8 13.2 16.8H10V7.2ZM11.8 11.1H13C13.9 11.1 14.4 10.6 14.4 9.9C14.4 9.2 13.9 8.8 13 8.8H11.8V11.1ZM11.8 15.2H13.1C14.1 15.2 14.7 14.7 14.7 13.9C14.7 13.1 14.1 12.6 13.1 12.6H11.8V15.2Z" fill="currentColor"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Admin Sign In</h1>
            <p class="text-sm text-gray-500 mt-1">Be Better BSBL — Product Management</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-3 border border-green-200">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-3 border border-red-200">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
            </div>

            <div class="flex items-center justify-between mb-5">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300" />
                    Remember me
                </label>
            </div>

            <button type="submit"
                    class="w-full rounded-md bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                Sign in
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-6">
            <a href="/" class="hover:text-gray-600">&larr; Back to store</a>
        </p>
    </div>
</body>
</html>

