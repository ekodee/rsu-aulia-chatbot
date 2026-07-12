<aside class="w-64 bg-primary text-white flex flex-col">

    <div class="p-5">
        <h1 class="text-lg font-bold">RSU Aulia</h1>
        <p class="text-sm opacity-70">Chatbot Admin</p>
    </div>

    <nav class="flex-1 px-3 space-y-2 text-sm">

        <a href="{{ route('admin.dashboard') }}"
            class="block p-3 rounded-lg 
           {{ request()->routeIs('admin.dashboard') ? 'bg-primary-light' : 'hover:bg-primary-light' }}">
            Dashboard
        </a>

        <a href="{{ route('admin.scraper') }}"
            class="block p-3 rounded-lg 
           {{ request()->routeIs('admin.scraper') ? 'bg-primary-light' : 'hover:bg-primary-light' }}">
            Website Scraper
        </a>

        <a href="{{ route('admin.pdf') }}"
            class="block p-3 rounded-lg 
           {{ request()->routeIs('admin.pdf') ? 'bg-primary-light' : 'hover:bg-primary-light' }}">
            PDF Upload
        </a>

        <a href="{{ route('admin.users') }}"
            class="block p-3 rounded-lg 
           {{ request()->routeIs('admin.users') ? 'bg-primary-light' : 'hover:bg-primary-light' }}">
            Users
        </a>

    </nav>

</aside>
