<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatHistoryController extends Controller
{
    // 1. Mengambil daftar riwayat (sidebar kiri)
    public function index(Request $request)
    {
        $sessions = ChatSession::where('user_id', $request->user()->id)
            ->latest() // Urutkan dari yang terbaru
            ->get();

        return response()->json($sessions);
    }

    // 2. Mengambil isi percakapan saat salah satu riwayat diklik
    public function show(Request $request, $id)
    {
        $session = ChatSession::where('user_id', $request->user()->id)->findOrFail($id);

        // Ambil pesannya dari yang paling lama ke terbaru agar urut dibaca
        $messages = $session->messages()->oldest()->get();

        return response()->json([
            'session' => $session,
            'messages' => $messages
        ]);
    }

    // 3. Menyimpan obrolan baru
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|exists:chat_sessions,id',
            'user_message' => 'required|string',
            'ai_message' => 'required|string',
        ]);

        $sessionId = $request->session_id;

        // Jika ini percakapan baru (belum ada session_id), buatkan judulnya!
        if (!$sessionId) {
            // Ambil 30 karakter pertama dari pertanyaan user sebagai judul
            $title = substr($request->user_message, 0, 30) . (strlen($request->user_message) > 30 ? '...' : '');

            $session = ChatSession::create([
                'user_id' => $request->user()->id,
                'title' => $title
            ]);
            $sessionId = $session->id;
        }

        // Simpan Pertanyaan User
        ChatMessage::create([
            'chat_session_id' => $sessionId,
            'role' => 'user',
            'content' => $request->user_message
        ]);

        // Simpan Jawaban AI
        ChatMessage::create([
            'chat_session_id' => $sessionId,
            'role' => 'ai',
            'content' => $request->ai_message
        ]);

        return response()->json([
            'message' => 'Riwayat berhasil disimpan',
            'session_id' => $sessionId
        ]);
    }

    // 4. Menghapus obrolan spesifik
    public function destroy(Request $request, $id)
    {
        // Cari sesi obrolan milik user yang sedang login
        $session = ChatSession::where('user_id', $request->user()->id)->findOrFail($id);

        // Hapus semua pesan di dalam sesi tersebut terlebih dahulu
        // (Opsional jika Anda sudah menggunakan 'onDelete cascade' di migration)
        $session->messages()->delete();

        // Hapus sesi obrolannya
        $session->delete();

        return response()->json([
            'message' => 'Riwayat percakapan berhasil dihapus'
        ]);
    }
}
