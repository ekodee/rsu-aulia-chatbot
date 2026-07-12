<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        // ========== STATISTIK KNOWLEDGE BASE ==========
        $knowledgeBases = KnowledgeBase::latest()->get();

        $totalKnowledge = KnowledgeBase::count();
        $totalPdf = KnowledgeBase::where('type', 'pdf')->count();
        $totalScrap = KnowledgeBase::whereIn('type', ['artikel', 'jadwal_dokter'])->count();
        $totalSuccess = KnowledgeBase::where('status', 'success')->count();
        $failedCount = KnowledgeBase::where('status', 'failed')->count();

        // ========== STATISTIK CHAT MESSAGES ==========
        $totalUserQuestions = ChatMessage::where('role', 'user')->count();

        // ========== 5 DATA TERAKHIR ==========
        $recentData = KnowledgeBase::latest()->take(5)->get();

        // ========== GRAFIK 30 HARI ==========
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $rawData = ChatMessage::where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $dailyData = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');

            if ($rawData->has($dateKey)) {
                $dailyData->push((object) [
                    'date' => $dateKey,
                    'day' => $date->day,
                    'month' => $date->month,
                    'total' => $rawData->get($dateKey)->total
                ]);
            } else {
                $dailyData->push((object) [
                    'date' => $dateKey,
                    'day' => $date->day,
                    'month' => $date->month,
                    'total' => 0
                ]);
            }
        }

        return view('admin.dashboard', compact(
            'knowledgeBases',
            'totalKnowledge',
            'totalPdf',
            'totalScrap',
            'totalSuccess',
            'failedCount',
            'totalUserQuestions',
            'recentData',
            'dailyData'
        ));
    }

    public function store(Request $request)
    {
        // 1. Validasi input dari Admin
        $request->validate([
            'url' => 'required|url',
            'type' => 'required|in:artikel,jadwal_dokter',
        ]);

        // 2. Simpan ke database MySQL dengan status 'pending'
        $kb = KnowledgeBase::create([
            'url' => $request->url,
            'type' => $request->type,
            'status' => 'pending',
            'message' => 'Sedang memproses...',
            'created_by' => auth()->user()->id, // TAMBAHKAN: ID user yang memasukkan
        ]);

        // 3. Tentukan Endpoint FastAPI berdasarkan tipe
        $fastApiUrl = $request->type === 'jadwal_dokter'
            ? 'http://127.0.0.1:8000/api/ingest/jadwal'
            : 'http://127.0.0.1:8000/api/ingest/url';

        try {
            // 4. Tembak API FastAPI (Timeout 60 detik karena AI butuh waktu membaca)
            $response = Http::timeout(60)->post($fastApiUrl, [
                'url' => $request->url
            ]);

            // 5. Update status di MySQL berdasarkan jawaban FastAPI
            if ($response->successful()) {
                $kb->update([
                    'status' => 'success',
                    'message' => $response->json('message') ?? 'Berhasil disimpan ke Astra DB'
                ]);
            } else {
                $kb->update([
                    'status' => 'failed',
                    'message' => 'Gagal: ' . $response->body()
                ]);
            }
        } catch (\Exception $e) {
            // Jika FastAPI mati atau error jaringan
            $kb->update([
                'status' => 'failed',
                'message' => 'Error Koneksi ke AI Engine: ' . $e->getMessage()
            ]);
        }

        return redirect()->back()->with('status', 'Proses scraping selesai!');
    }

    public function destroy($id)
    {
        $kb = KnowledgeBase::findOrFail($id);
        $source = $kb->url;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->post('http://127.0.0.1:8000/api/knowledge/delete', [
                    'source' => $source
                ]);

            if ($response->successful()) {
                $kb->delete();
                return redirect()->back()->with('status', 'Data berhasil dihapus dari sistem dan memori AI!');
            } else {
                return redirect()->back()->with('error', 'Gagal menghapus memori di AI: ' . $response->body());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error Koneksi ke AI Engine: ' . $e->getMessage());
        }
    }

    public function createPdf()
    {
        $knowledgeBases = KnowledgeBase::where('type', 'pdf')
            ->with('creator') // Eager loading relasi
            ->latest()
            ->get();

        return view('admin.upload-pdf', compact('knowledgeBases'));
    }

    public function storePdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $file = $request->file('pdf_file');
        $filename = $file->getClientOriginalName();

        $kb = \App\Models\KnowledgeBase::create([
            'url' => $filename,
            'type' => 'pdf',
            'status' => 'pending',
            'message' => 'Sedang memproses dokumen...',
            'created_by' => auth()->user()->id, // TAMBAHKAN: ID user yang memasukkan
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(120)->attach(
                'file',
                file_get_contents($file),
                $filename
            )->post('http://127.0.0.1:8000/api/ingest/pdf');

            if ($response->successful()) {
                $kb->update([
                    'status' => 'success',
                    'message' => $response->json('message') ?? 'Berhasil diproses AI'
                ]);
            } else {
                $kb->update([
                    'status' => 'failed',
                    'message' => 'Gagal AI: ' . $response->body()
                ]);
            }
        } catch (\Exception $e) {
            $kb->update([
                'status' => 'failed',
                'message' => 'Error Koneksi: ' . $e->getMessage()
            ]);
        }

        return redirect()->back()->with('status', 'Dokumen sedang/telah diproses oleh AI!');
    }

    public function createScrap()
    {
        $knowledgeBases = KnowledgeBase::whereIn('type', ['artikel', 'jadwal_dokter'])
            ->with('creator') // Eager loading relasi
            ->latest()
            ->get();

        return view('admin.scrap-web', compact('knowledgeBases'));
    }
}
