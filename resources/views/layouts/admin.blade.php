<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.wa-users.index') }}" class="text-xl font-bold text-gray-900">
                        {{ config('app.name') }}
                    </a>
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="{{ route('admin.wa-users.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.wa-users.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            WA Users
                        </a>
                        <a href="{{ route('admin.settings.edit') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            Settings
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-500 mr-4">{{ auth()->user()->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                <ul class="list-disc list-inside text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
