@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-bold mb-6">User Management</h1>

    <!-- ALERT STATUS -->
    @if (session('status'))
        <div class="mb-6 bg-green-100 text-green-700 p-4 rounded-lg font-medium">
            ✅ {{ session('status') }}
        </div>
    @endif

    <!-- ALERT ERROR VALIDASI -->
    @if ($errors->any())
        <div class="mb-6 bg-red-100 text-red-700 p-4 rounded-lg">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="font-semibold">Daftar User</h2>
        <button onclick="openModal('createModal')" class="bg-primary text-white px-4 py-2 rounded-lg hover:opacity-90">
            + Tambah User
        </button>
    </div>

    <!-- TABLE -->
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-400 border-b">
                    <tr>
                        <th class="text-left py-2">Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($users as $user)
                        <tr>
                            <td class="py-3 font-medium">{{ $user->name }}</td>
                            <td class="text-center">{{ $user->email }}</td>
                            <td class="text-center">
                                @if($user->role === 'admin')
                                    <x-admin.badge type="warning">Admin</x-admin.badge>
                                @else
                                    <x-admin.badge type="success">User</x-admin.badge>
                                @endif
                            </td>
                            <td class="text-center space-x-2">
                                <button onclick="openModal('editModal{{ $user->id }}')" class="text-blue-500 font-semibold hover:underline">Edit</button>
                                <button onclick="openModal('deleteModal{{ $user->id }}')" class="text-red-500 font-semibold hover:underline">Delete</button>
                            </td>
                        </tr>

                        <!-- ================= MODAL EDIT & DELETE PER USER ================= -->

                        <!-- EDIT MODAL -->
                        <div id="editModal{{ $user->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded-xl p-6 w-96 text-left">
                                <h2 class="font-semibold mb-4">Edit User</h2>
                                <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    
                                    <label class="text-xs text-gray-500">Nama</label>
                                    <input type="text" name="name" value="{{ $user->name }}" required class="w-full border px-3 py-2 rounded-lg">
                                    
                                    <label class="text-xs text-gray-500">Email</label>
                                    <input type="email" name="email" value="{{ $user->email }}" required class="w-full border px-3 py-2 rounded-lg">
                                    
                                    <label class="text-xs text-gray-500">Password Baru (Opsional)</label>
                                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full border px-3 py-2 rounded-lg">

                                    <label class="text-xs text-gray-500">Role</label>
                                    <select name="role" class="w-full border px-3 py-2 rounded-lg">
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                    </select>

                                    <div class="flex justify-end gap-2 pt-3">
                                        <button type="button" onclick="closeModal('editModal{{ $user->id }}')" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:opacity-90">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- DELETE MODAL -->
                        <div id="deleteModal{{ $user->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded-xl p-6 w-80 text-center">
                                <h2 class="font-semibold mb-2">Hapus User?</h2>
                                <p class="text-sm text-gray-500 mb-6">Apakah kamu yakin ingin menghapus <b>{{ $user->name }}</b> secara permanen?</p>
                                
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="flex justify-center gap-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="closeModal('deleteModal{{ $user->id }}')" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Hapus</button>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-400">Belum ada user terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <!-- ================= MODAL TAMBAH USER ================= -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-96">
            <h2 class="font-semibold mb-4">Tambah User Baru</h2>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Nama Lengkap" required class="w-full border px-3 py-2 rounded-lg focus:ring-2 focus:ring-primary">
                <input type="email" name="email" placeholder="Alamat Email" required class="w-full border px-3 py-2 rounded-lg focus:ring-2 focus:ring-primary">
                <input type="password" name="password" placeholder="Password" required class="w-full border px-3 py-2 rounded-lg focus:ring-2 focus:ring-primary">
                
                <select name="role" required class="w-full border px-3 py-2 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="" disabled selected>-- Pilih Role --</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:opacity-90">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>
@endsection