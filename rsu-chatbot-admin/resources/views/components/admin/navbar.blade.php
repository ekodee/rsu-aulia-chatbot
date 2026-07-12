<header class="bg-white px-6 py-4 flex justify-end items-center shadow-sm">

    <!-- User Dropdown -->
    <div x-data="{ open: false }" class="relative">

        <!-- BUTTON -->
        <button @click="open = !open" class="flex items-center gap-3 focus:outline-none">

            <!-- Avatar -->
            <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center font-semibold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <!-- Name -->
            <div class="text-left hidden md:block">
                <p class="text-sm font-semibold leading-none">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-xs text-gray-400">
                    Admin
                </p>
            </div>

            <!-- Arrow -->
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>

        </button>

        <!-- DROPDOWN -->
        <div x-show="open" @click.outside="open = false" x-transition
            class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border overflow-hidden">

            <!-- USER INFO -->
            <div class="px-4 py-3 border-b">
                <p class="text-sm font-semibold">
                    {{ Auth::user()->name }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ Auth::user()->email }}
                </p>
            </div>

            <!-- MENU -->
            <a href="/profile" class="block px-4 py-2 text-sm hover:bg-gray-100">
                Profile
            </a>

            <!-- LOGOUT -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-500">
                    Logout
                </button>
            </form>

        </div>

    </div>

</header>
