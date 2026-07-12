{{-- resources/views/errors/403.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <!-- Asumsi menggunakan Tailwind CSS. Jika pakai Bootstrap, sesuaikan class-nya -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen font-sans text-gray-900">
    <div class="bg-white p-8 rounded-xl shadow-lg text-center max-w-md w-full border border-gray-200">
        <div class="text-red-500 mb-4">
            <!-- Icon Alert/Warning -->
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
        </div>
        <h1 class="text-5xl font-extrabold text-gray-800 mb-2">403</h1>
        <h2 class="text-xl font-bold text-gray-700 mb-2">Akses Ditolak!</h2>
        <p class="text-gray-500 mb-8 text-sm">
            Maaf, akun Anda saat ini tidak memiliki hak akses tingkat administrator untuk membuka halaman kontrol panel
            ini.
        </p>

        <!-- Form Logout Paksa -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors shadow-sm">
                Logout dan Kembali ke Login
            </button>
        </form>
    </div>
</body>

</html>
