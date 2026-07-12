<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RSU Aulia Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-background text-text">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <x-admin.sidebar />

        <!-- Main -->
        <div class="flex-1 flex flex-col">

            <!-- Navbar -->
            <x-admin.navbar />

            <!-- Content -->
            <main class="p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>

    </div>

</body>

</html>
