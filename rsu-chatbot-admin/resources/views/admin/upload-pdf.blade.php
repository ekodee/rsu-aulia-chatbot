@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-bold mb-6 text-gray-900">Upload PDF</h1>

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

    <!-- UPLOAD -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">

        <h2 class="font-semibold text-gray-800 mb-4">Upload Dokumen</h2>

        <p class="text-sm text-gray-500 mb-6">
            Upload pedoman, brosur, atau aturan RS (max 10MB)
        </p>

        <form action="{{ route('knowledge.storePdf') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <!-- DROP AREA -->
            <div id="dropArea"
                class="border-2 border-dashed border-gray-300 rounded-xl p-10 text-center cursor-pointer hover:border-green-500 transition-colors">

                <!-- Ikon -->
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>

                <p id="dropText" class="text-gray-600 font-medium">Drag & drop PDF di sini</p>
                <p class="text-sm text-gray-400 mb-4">atau klik untuk pilih file (max 10MB)</p>

                <!-- Nama file yang dipilih -->
                <p id="fileName" class="text-green-600 font-medium mt-2 hidden"></p>

                <!-- Input File -->
                <input type="file" name="pdf_file" id="fileInput" accept="application/pdf" class="hidden" required>

            </div>

            @error('pdf_file')
                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror

            <!-- SUBMIT -->
            <div class="mt-6 flex justify-between items-center">
                <span id="fileStatus" class="text-sm text-gray-400">Belum ada file dipilih</span>
                <button type="submit" id="uploadBtn"
                    onclick="this.innerHTML='Uploading...'; this.classList.add('opacity-50')"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Upload
                </button>
            </div>

        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-gray-800">Riwayat Dokumen PDF</h2>
            <span class="text-sm text-gray-400">{{ $knowledgeBases->count() }} file</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-400 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-2 px-3">Nama File</th>
                        <th class="text-center py-2 px-3">Status</th>
                        <th class="text-left py-2 px-3 max-w-xs">Pesan</th>
                        <th class="text-left py-2 px-3">Diinput Oleh</th>
                        <th class="text-center py-2 px-3">Tanggal Upload</th>
                        <th class="text-center py-2 px-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($knowledgeBases as $kb)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-3 text-green-600 font-medium">
                                <span title="{{ $kb->url }}">{{ Str::limit($kb->url, 30) }}</span>
                            </td>
                            <td class="text-center py-3 px-3">
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
                            <td class="py-3 px-3 max-w-xs truncate text-gray-500" title="{{ $kb->message }}">
                                {{ Str::limit($kb->message, 45) }}
                            </td>
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
                            <td class="text-center py-3 px-3 text-gray-500">
                                {{ $kb->created_at->diffForHumans() }}
                            </td>
                            <td class="text-center py-3 px-3">
                                <form action="{{ route('knowledge.destroy', $kb->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus dokumen ini dari ingatan AI?');">
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
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Belum ada dokumen PDF yang diupload
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- INFORMASI TAMBAHAN -->
        <div class="mt-4 text-xs text-gray-400 text-center">
            Total {{ $knowledgeBases->count() }} file |
            Berhasil: {{ $knowledgeBases->where('status', 'success')->count() }} |
            Gagal: {{ $knowledgeBases->where('status', 'failed')->count() }}
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropArea = document.getElementById('dropArea');
            const fileInput = document.getElementById('fileInput');
            const fileName = document.getElementById('fileName');
            const dropText = document.getElementById('dropText');
            const fileStatus = document.getElementById('fileStatus');
            const uploadBtn = document.getElementById('uploadBtn');

            // 1. PENCEGAHAN DOUBLE CLICK
            dropArea.addEventListener('click', function(e) {
                if (e.target.closest('button')) return;

                if (fileInput.files && fileInput.files.length > 0) {
                    if (confirm('File "' + fileInput.files[0].name +
                            '" sudah dipilih. Ingin mengganti dengan file lain?')) {
                        resetFileInput();
                        fileInput.click();
                    }
                } else {
                    fileInput.click();
                }
            });

            // 2. EVENT CHANGE
            fileInput.addEventListener('change', function(e) {
                const file = this.files[0];
                if (!file) {
                    resetFileInput();
                    return;
                }

                // Validasi tipe file
                if (file.type !== 'application/pdf') {
                    alert('Hanya file PDF yang diperbolehkan!');
                    resetFileInput();
                    return;
                }

                // Validasi ukuran (max 10MB = 10485760 bytes)
                if (file.size > 10485760) {
                    alert('Ukuran file maksimal 10MB!');
                    resetFileInput();
                    return;
                }

                // Tampilkan nama file
                const fileSize = (file.size / 1024).toFixed(1);
                fileName.textContent = file.name + ' (' + fileSize + ' KB)';
                fileName.classList.remove('hidden');
                dropText.textContent = 'File siap diupload!';
                dropText.classList.add('text-green-600');
                fileStatus.textContent = file.name;
                fileStatus.classList.remove('text-gray-400');
                fileStatus.classList.add('text-green-600');
                uploadBtn.disabled = false;
            });

            // 3. DRAG & DROP
            dropArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('border-green-500', 'bg-green-50');
            });

            dropArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('border-green-500', 'bg-green-50');
            });

            dropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('border-green-500', 'bg-green-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];

                    if (file.type !== 'application/pdf') {
                        alert('Hanya file PDF yang diperbolehkan!');
                        return;
                    }

                    if (file.size > 10485760) {
                        alert('Ukuran file maksimal 10MB!');
                        return;
                    }

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    const event = new Event('change', {
                        bubbles: true
                    });
                    fileInput.dispatchEvent(event);
                }
            });

            // 4. FUNGSI RESET
            function resetFileInput() {
                fileInput.value = '';
                fileName.classList.add('hidden');
                dropText.textContent = 'Drag & drop PDF di sini';
                dropText.classList.remove('text-green-600');
                fileStatus.textContent = 'Belum ada file dipilih';
                fileStatus.classList.remove('text-green-600');
                fileStatus.classList.add('text-gray-400');
                uploadBtn.disabled = true;
            }

            // 5. CEK STATUS AWAL
            if (!fileInput.files || fileInput.files.length === 0) {
                uploadBtn.disabled = true;
            }
        });
    </script>
@endsection
