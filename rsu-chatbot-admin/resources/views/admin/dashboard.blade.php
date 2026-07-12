@extends('layouts.admin')

@section('content')
    <!-- Header Dasbor -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Statistik</h1>
        <p class="text-gray-500 mt-1">Selamat datang kembali, <b>{{ Auth::user()->name }}</b>. Berikut ringkasan memori
            kecerdasan buatan (AI) saat ini.</p>
    </div>

    <!-- Grid Kartu Metrik (5 Card) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">

        <!-- 1. Total Knowledge -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Injeksi Data</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalKnowledge }}</p>
            </div>
        </div>

        <!-- 2. Total PDF -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Dokumen PDF</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalPdf }}</p>
            </div>
        </div>

        <!-- 3. Total Scraping -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="p-3 rounded-full bg-teal-100 text-teal-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Scraping Web</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalScrap }}</p>
            </div>
        </div>

        <!-- 4. Data Sukses -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-all hover:shadow-md">
            <div
                class="p-3 rounded-full {{ $failedCount > 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }} mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Data Sukses di AI</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalSuccess }}</p>
                @if ($failedCount > 0)
                    <p class="text-xs text-red-500 font-medium mt-1">{{ $failedCount }} data gagal diproses!</p>
                @endif
            </div>
        </div>

        <!-- 5. Total Pertanyaan User (CARD BARU) -->
        <div
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center transition-all hover:shadow-md">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Pertanyaan User</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalUserQuestions ?? 0 }}</p>
                <p class="text-xs text-gray-400 mt-1">Sejak sistem beroperasi</p>
            </div>
        </div>
    </div>

    <!-- ========== GRAFIK 30 HARI TERAKHIR ========== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Tren Pertanyaan User (30 Hari Terakhir)</h2>
            <span class="text-xs text-gray-400">{{ now()->subDays(29)->format('d M') }} -
                {{ now()->format('d M Y') }}</span>
        </div>

        @php
            $hasData = $dailyData->sum('total') > 0;
            $maxCount = $dailyData->max('total') ?: 1;
            $totalQuestions = $dailyData->sum('total');
            $daysWithData = $dailyData->filter(fn($d) => $d->total > 0)->count();
        @endphp

        @if ($hasData)
            <div class="relative h-48 flex items-end gap-1">
                @foreach ($dailyData as $index => $data)
                    @php
                        $heightPercentage = ($data->total / $maxCount) * 100;
                        $heightPercentage = max($heightPercentage, 4);
                        $isWeekend = in_array(date('N', strtotime($data->date)), [6, 7]);
                    @endphp
                    <div class="flex-1 flex flex-col items-center h-full justify-end group">
                        <!-- Tooltip (muncul saat hover) -->
                        <div
                            class="opacity-0 group-hover:opacity-100 transition-opacity text-xs bg-gray-800 text-white px-2 py-1 rounded absolute -mt-12 z-10">
                            {{ date('d M', strtotime($data->date)) }}: {{ $data->total }} pertanyaan
                        </div>
                        <!-- Nilai angka di atas batang (hanya jika > 0) -->
                        @if ($data->total > 0)
                            <div class="text-xs font-bold text-green-600 mb-0.5">{{ $data->total }}</div>
                        @endif
                        <!-- Batang grafik -->
                        <div class="w-full rounded-t transition-all duration-300 cursor-pointer
                        {{ $data->total > 0
                            ? ($isWeekend
                                ? 'bg-orange-400 hover:bg-orange-500'
                                : 'bg-green-400 hover:bg-green-500')
                            : 'bg-gray-100' }}"
                            style="height: {{ $heightPercentage }}%; min-height: 4px;">
                            <div class="w-full h-full rounded-t transition-all duration-300
                            {{ $data->total > 0
                                ? ($isWeekend
                                    ? 'bg-orange-500 hover:bg-orange-600'
                                    : 'bg-green-500 hover:bg-green-600')
                                : 'bg-gray-200' }}"
                                style="height: {{ $heightPercentage }}%; min-height: 4px;">
                            </div>
                        </div>
                        <!-- Label tanggal (hanya tampil setiap 5 hari) -->
                        <div
                            class="text-[10px] text-gray-400 mt-1 {{ $index % 5 == 0 ? 'font-medium' : 'opacity-0 group-hover:opacity-100' }}">
                            {{ date('d', strtotime($data->date)) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Ringkasan -->
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap justify-center gap-4 text-sm">
                <span class="text-gray-500">Total: <b class="text-gray-900">{{ $totalQuestions }}</b> pertanyaan</span>
                <span class="text-gray-500">Hari aktif: <b class="text-gray-900">{{ $daysWithData }}</b> dari 30
                    hari</span>
                <span class="text-gray-500">Rata-rata: <b
                        class="text-gray-900">{{ $daysWithData > 0 ? round($totalQuestions / $daysWithData, 1) : 0 }}</b>
                    pertanyaan/hari</span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-green-500 rounded"></span>
                    <span class="text-gray-500 text-xs">Hari Kerja</span>
                    <span class="inline-block w-3 h-3 bg-orange-500 rounded ml-2"></span>
                    <span class="text-gray-500 text-xs">Akhir Pekan</span>
                </span>
            </div>
        @else
            <p class="text-gray-400 text-center py-12">Belum ada data pertanyaan dari user dalam 30 hari terakhir.</p>
        @endif
    </div>

    <!-- ========== AKSI CEPAT ========== -->
    <h2 class="text-xl font-bold text-gray-900 mb-4">Aksi Cepat</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <!-- Shortcut Scraping -->
        <a href="{{ route('admin.scraper') }}"
            class="group block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all hover:border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600">Scraping Web</h3>
                    <p class="text-sm text-gray-500 mt-1">Ekstrak artikel & jadwal dokter.</p>
                </div>
                <div class="text-gray-300 group-hover:text-green-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3">
                        </path>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Shortcut PDF -->
        <a href="{{ route('admin.pdf') }}"
            class="group block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all hover:border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600">Upload PDF</h3>
                    <p class="text-sm text-gray-500 mt-1">Suntikkan pedoman / brosur RS.</p>
                </div>
                <div class="text-gray-300 group-hover:text-green-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </div>
            </div>
        </a>

        <!-- Shortcut Users -->
        <a href="{{ route('admin.users') }}"
            class="group block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all hover:border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 group-hover:text-green-600">Manajemen User</h3>
                    <p class="text-sm text-gray-500 mt-1">Kelola akses admin & pasien.</p>
                </div>
                <div class="text-gray-300 group-hover:text-green-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    <!-- ========== 5 DATA TERAKHIR ========== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">5 Data Terakhir yang diinput</h2>
            <a href="{{ route('admin.scraper') }}"
                class="text-sm text-green-600 hover:text-green-800 font-medium transition-colors">Lihat semua →</a>
        </div>

        @if (isset($recentData) && $recentData->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-200 text-left">
                            <th class="py-3 px-4 font-semibold text-gray-600">No</th>
                            <th class="py-3 px-4 font-semibold text-gray-600">Sumber Data</th>
                            <th class="py-3 px-4 font-semibold text-gray-600">Tipe</th>
                            <th class="py-3 px-4 font-semibold text-gray-600">Tanggal Input</th>
                            <th class="py-3 px-4 font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentData as $index => $data)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 text-gray-700 font-medium">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 text-gray-700 max-w-xs truncate" title="{{ $data->url }}">
                                    {{ Str::limit($data->url, 55) }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($data->type == 'pdf')
                                        <span
                                            class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">PDF</span>
                                    @elseif($data->type == 'jadwal_dokter')
                                        <span
                                            class="px-2.5 py-1 text-xs font-medium rounded-full bg-teal-100 text-teal-700">Jadwal</span>
                                    @elseif($data->type == 'artikel')
                                        <span
                                            class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Artikel</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">{{ $data->type }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $data->created_at->translatedFormat('d F Y H:i') }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($data->status == 'success')
                                        <span class="inline-flex items-center gap-1 text-green-600 font-medium">

                                            Berhasil
                                        </span>
                                    @elseif($data->status == 'failed')
                                        <span class="inline-flex items-center gap-1 text-red-600 font-medium">

                                            Gagal
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-yellow-600 font-medium">
                                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                    </path>
                </svg>
                <p class="text-gray-400">Belum ada data yang di-insert ke knowledge base.</p>
                <p class="text-gray-300 text-sm mt-1">Mulai dengan melakukan scraping web atau upload PDF.</p>
            </div>
        @endif
    </div>

@endsection
