<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem AI RSU Aulia</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased selection:bg-green-500 selection:text-white">

    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo-rs-aulia.png') }}" alt="Logo RSU Aulia" class="h-8 w-auto mx-auto">
                    <span class="font-bold text-xl text-gray-900">RSU Aulia AI</span>
                </div>
                <div>
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="text-sm font-semibold text-green-600 hover:text-green-800 transition-colors">
                            Ke Dasbor Admin &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex justify-center items-center px-8 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-green-600 hover:bg-green-700 md:text-lg transition-all shadow-md hover:shadow-lg">
                            Login Staf
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Bagian Hero / Utama -->
    <div class="relative overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-6">
                Layanan Informasi Kesehatan <br class="hidden md:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-emerald-500">Berbasis
                    Kecerdasan Buatan</span>
            </h1>
            <p class="mt-4 max-w-2xl text-lg text-gray-500 mx-auto mb-10 leading-relaxed">
                Sistem Chatbot Cerdas RSU Aulia. Dapatkan informasi jadwal praktik dokter, layanan medis, dan panduan
                pasien secara instan, 24 jam penuh.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <!-- Tombol ke Next.js (Sisi Pengguna/Pasien) -->
                <a href="http://localhost:3000" target="_blank"
                    class="inline-flex justify-center items-center px-8 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-green-600 hover:bg-green-700 md:text-lg transition-all shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    Coba Chatbot Pasien
                </a>

                <!-- Tombol ke Login Admin (Sisi Backend Laravel) -->
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex justify-center items-center px-8 py-3 border-2 border-gray-200 text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 md:text-lg transition-all">
                        Buka Dasbor Admin
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex justify-center items-center px-8 py-3 border-2 border-gray-200 text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 md:text-lg transition-all">
                        Portal Administrator
                    </a>
                @endauth
            </div>
        </div>

        <!-- Efek Latar Belakang Gradasi Tipis -->
        <div class="absolute top-0 -z-10 h-full w-full bg-white">
            <div
                class="absolute bottom-auto left-auto right-0 top-0 h-[500px] w-[500px] -translate-x-[30%] translate-y-[20%] rounded-full bg-green-100 opacity-50 blur-[80px]">
            </div>
        </div>
    </div>

    <!-- Bagian Fitur Singkat -->
    <div class="bg-gray-50 py-16 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <!-- Fitur 1 -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 mx-auto bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Aktif 24/7</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Asisten virtual siap melayani pertanyaan pasien
                        kapan saja tanpa hambatan waktu operasional.</p>
                </div>
                <!-- Fitur 2 -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 mx-auto bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Informasi Akurat</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Setiap jawaban bersumber dari dokumen resmi RSU
                        Aulia, sehingga informasi yang diberikan selalu akurat dan terpercaya.</p>
                </div>
                <!-- Fitur 3 -->
                <div
                    class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 mx-auto bg-teal-100 text-teal-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Data Terpusat</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Kemudahan pengelolaan basis pengetahuan medis
                        melalui satu pintu dasbor administrator.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} Sistem Informasi RSU Aulia.</p>
        </div>
    </footer>

</body>

</html>
