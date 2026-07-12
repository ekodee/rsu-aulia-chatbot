import json
import requests
from bs4 import BeautifulSoup

def ekstrak_jadwal_dokter(url: str, output_filename: str = "hasil_jadwal.json"):
    print(f"🔍 [TABLE MODE] Menyedot Jadwal Dokter dari: {url}")
    
    try:
        response = requests.get(url, headers={"User-Agent": "Mozilla/5.0"})
        soup = BeautifulSoup(response.content, "html.parser")
        
        hasil_kalimat = []
        
        # 1. Cari semua Judul Poli (Spesialisasi)
        # Menggunakan class elementor-tab-title bawaan EAEL Accordion
        accordion_headers = soup.find_all("div", class_="elementor-tab-title")
        
        for header in accordion_headers:
            # Ambil nama poli, misal: "Spesialis Anak"
            poli = header.text.strip()
            
            # Cari ID konten pembungkus tabel yang terhubung
            content_id = header.get("aria-controls")
            if not content_id:
                continue
                
            content_div = soup.find("div", id=content_id)
            if not content_div:
                continue
                
            # 2. Cari tabel di dalam kotak Poli tersebut
            table = content_div.find("table")
            if not table:
                continue
                
            # 3. Ekstrak Baris per Baris (Row-to-Sentence)
            # Cari tbody, lalu cari semua tr (baris)
            tbody = table.find("tbody")
            rows = tbody.find_all("tr") if tbody else table.find_all("tr")
            
            for row in rows:
                cols = row.find_all("td")
                if not cols:
                    continue
                
                # Buat dictionary kosong untuk menyimpan data satu dokter
                data_dokter = {}
                for col in cols:
                    # Ini kehebatan data-label dari HTML kamu!
                    label = col.get("data-label", "").strip()
                    val = col.text.strip()
                    if label:
                        data_dokter[label] = val
                
                nama_dokter = data_dokter.get("Dokter", "")
                if not nama_dokter:
                    continue
                
                # 4. Merakit Kalimat yang Enak Dibaca AI
                jadwal_hari = []
                for hari in ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]:
                    val = data_dokter.get(hari, "-")
                    # Kita hanya ambil yang ada jadwalnya (Bukan strip / libur)
                    if val and val != "-" and val.lower() != "libur":
                        jadwal_hari.append(f"{hari} jam {val}")
                
                catatan = data_dokter.get("Catatan", "-")
                catatan_teks = f" (Catatan: {catatan})" if catatan and catatan != "-" else ""
                
                # Gabungkan menjadi satu kalimat utuh!
                if jadwal_hari:
                    jadwal_str = ", ".join(jadwal_hari)
                    kalimat = f"Jadwal Praktik {nama_dokter} di Poli {poli} adalah pada hari {jadwal_str}.{catatan_teks}"
                else:
                    kalimat = f"{nama_dokter} di Poli {poli} saat ini belum ada jadwal / libur."
                
                hasil_kalimat.append(kalimat)

        # 5. SIMPAN KE JSON UNTUK DIINSPEKSI
        output_data = {
            "source_url": url,
            "total_dokter": len(hasil_kalimat),
            "data_jadwal": hasil_kalimat
        }

        with open(output_filename, "w", encoding="utf-8") as f:
            json.dump(output_data, f, ensure_ascii=False, indent=4)

        print(f"✅ Selesai! Berhasil merakit {len(hasil_kalimat)} kalimat jadwal.")
        print(f"📁 Buka file '{output_filename}' untuk melihat keajaibannya!\n")

    except Exception as e:
        print(f"❌ Terjadi kesalahan: {e}")

if __name__ == "__main__":
    # URL Halaman Jadwal Praktik RSU Aulia
    TARGET_URL = "https://rsaulia.com/jadwal-dokter/" 
    ekstrak_jadwal_dokter(TARGET_URL)