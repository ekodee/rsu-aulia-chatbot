@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-bold mb-6 text-gray-900">Website Scraper</h1>

    <!-- ALERT -->
    @if (session('status'))
        <div class="mb-6 bg-green-100 text-green-700 p-4 rounded-lg border border-green-200">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-red-100 text-red-700 p-4 rounded-lg border border-red-200 font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- FORM -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-800 mb-4">Tambah Knowledge Base</h2>

        <form action="{{ route('knowledge.store') }}" method="POST" class="flex flex-col md:flex-row gap-4">
            @csrf

            <!-- URL -->
            <div class="flex-1">
                <label class="text-sm text-gray-600 block mb-1">URL Website</label>
                <input type="url" name="url" required placeholder="https://rsaulia.com/..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                @error('url')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- TYPE -->
            <div class="w-full md:w-1/4">
                <label class="text-sm text-gray-600 block mb-1">Tipe</label>
                <select name="type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    <option value="artikel">Artikel</option>
                    <option value="jadwal_dokter">Jadwal Dokter</option>
                </select>
            </div>

            <!-- BUTTON -->
            <button type="submit" onclick="this.innerHTML='Memproses...'; this.classList.add('opacity-50')"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors h-[42px] self-end">
                Scrape
            </button>

        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-gray-800">Riwayat Knowledge</h2>
            <span class="text-sm text-gray-400">{{ $knowledgeBases->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-400 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-2 px-3">URL</th>
                        <th class="text-left py-2 px-3">Tipe</th>
                        <th class="text-left py-2 px-3">Status</th>
                        <th class="text-left py-2 px-3 max-w-xs">Pesan</th>
                        <th class="text-left py-2 px-3">Diinput Oleh</th>
                        <th class="text-left py-2 px-3">Tanggal</th>
                        <th class="text-center py-2 px-3">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($knowledgeBases as $kb)
                        <tr class="hover:bg-gray-50 transition-colors">

                            <!-- URL -->
                            <td class="py-3 px-3">
                                <a href="{{ $kb->url }}" target="_blank"
                                    class="text-green-600 hover:text-green-800 hover:underline max-w-xs truncate inline-block">
                                    {{ Str::limit($kb->url, 35) }}
                                </a>
                            </td>

                            <!-- TYPE -->
                            <td class="py-3 px-3">
                                @if ($kb->type === 'jadwal_dokter')
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium rounded-full bg-teal-100 text-teal-700">Jadwal</span>
                                @elseif($kb->type === 'pdf')
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">PDF</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Artikel</span>
                                @endif
                            </td>

                            <!-- STATUS -->
                            <td class="py-3 px-3">
                                @if ($kb->status === 'success')
                                    <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                        Sukses
                                    </span>
                                @elseif($kb->status === 'failed')
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

                            <!-- MESSAGE -->
                            <td class="py-3 px-3 max-w-xs truncate text-gray-500" title="{{ $kb->message }}">
                                {{ Str::limit($kb->message, 50) }}
                            </td>

                            <!-- ========== DIMASUKKAN OLEH (KOLOM BARU) ========== -->
                            <td class="py-3 px-3">
                                @if ($kb->creator)
                                    <span class="text-gray-700 font-medium">{{ $kb->creator->name }}</span>
                                    @if ($kb->creator->is_admin)
                                        <span class="text-xs text-gray-400 ml-1">(Admin)</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- DATE -->
                            <td class="py-3 px-3 text-gray-500">
                                {{ $kb->created_at->diffForHumans() }}
                            </td>

                            <!-- ACTION -->
                            <td class="py-3 px-3 text-center">
                                <form action="{{ route('knowledge.destroy', $kb->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus memori AI ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-600 font-semibold text-xs transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                Belum ada data scraping
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>
    </div>

    <!-- ========== INFORMASI TAMBAHAN ========== -->
    <div class="mt-4 text-xs text-gray-400 text-center">
        Total {{ $knowledgeBases->count() }} data |
        Berhasil: {{ $knowledgeBases->where('status', 'success')->count() }} |
        Gagal: {{ $knowledgeBases->where('status', 'failed')->count() }}
    </div>
@endsection
