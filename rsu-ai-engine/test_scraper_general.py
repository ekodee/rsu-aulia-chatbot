import json
import re
import requests
from bs4 import BeautifulSoup
from langchain_core.documents import Document
from langchain_text_splitters import RecursiveCharacterTextSplitter

def scrape_rsu_aulia(url: str, output_filename: str):
    print(f"🔍 [SNIPER MODE] Mengeksekusi URL: {url}")
    
    try:
        # 1. Sedot HTML Mentah
        response = requests.get(url, headers={"User-Agent": "Mozilla/5.0"})
        soup = BeautifulSoup(response.content, "html.parser")
        
        # 2. DEMOLITION MODE (Hancurkan semua sarang Noise WPBF & Elementor)
        # Menghancurkan tag standar
        for element in soup(["nav", "script", "style", "noscript", "meta"]):
            element.decompose()
            
        # Menghancurkan Header & Footer spesifik tema
        noise_selectors = [
            {"id": "header"}, {"class_": "wpbf-page-header"}, 
            {"class_": "wpbf-page-footer"}, {"data-elementor-type": "header"}, 
            {"data-elementor-type": "footer"}, {"class_": "elementor-location-header"},
            {"class_": "elementor-location-footer"}
        ]
        
        for selector in noise_selectors:
            for noise in soup.find_all(**selector):
                noise.decompose()

        # --- BOM PINTAR: MENGHANCURKAN SECTION "IKUTI KAMI" ---
        # Mencari teks "Ikuti Kami" di dalam HTML
        import re
        target_text = soup.find(string=re.compile("Ikuti Kami", re.IGNORECASE))
        if target_text:
            # Mencari pembungkus <section> utamanya (elementor-top-section) seperti di file txt
            section_to_destroy = target_text.find_parent("section", class_="elementor-top-section")
            if section_to_destroy:
                section_to_destroy.decompose()
                print("💣 Section 'Ikuti Kami' berhasil diledakkan!")
        # --------------------------------------------------------

        # 3. SNIPER MODE (Cari Konten Utama)
        # Prioritas 1: Tag <main> bawaan tema
        content_area = soup.find("main", id="main")
        
        # Prioritas 2: Pembungkus Elementor Page (jika main tidak ada)
        if not content_area:
            content_area = soup.find("div", attrs={"data-elementor-type": "wp-page"})
            
        # Prioritas 3: Pembungkus Elementor Post
        if not content_area:
            content_area = soup.find("div", attrs={"data-elementor-type": "wp-post"})

        # Jika tetap tidak ketemu, pakai sisa body yang sudah dibersihkan dari header/footer
        if not content_area:
            content_area = soup.find("body")

        # 4. AMBIL TEKS DENGAN PEMISAH SPASI (Menyembuhkan penyakit teks menempel seperti "Sp.Adr.")
        raw_text = content_area.get_text(separator=" ", strip=True)
        
        # 5. PROSES CLEANING REGEX (Sapu Bersih Spasi & Enter)
        clean_text = re.sub(r'\n+', ' ', raw_text)
        clean_text = re.sub(r'\s+', ' ', clean_text)
        
        # Bungkus teks bersih ke dalam format Document
        docs = [Document(page_content=clean_text, metadata={"source": url})]
        
        # 6. CHUNKING (Pemotongan)
        text_splitter = RecursiveCharacterTextSplitter(
            chunk_size=500,
            chunk_overlap=50
        )
        chunked_docs = text_splitter.split_documents(docs)
        print(f"✂️ Bersih! Teks dipotong menjadi {len(chunked_docs)} chunks.")

        # 7. SIMPAN KE JSON
        output_data = {
            "source_url": url,
            "total_chunks": len(chunked_docs),
            "chunks": []
        }

        for i, chunk in enumerate(chunked_docs):
            output_data["chunks"].append({
                "chunk_id": i + 1,
                "text_length": len(chunk.page_content),
                "content": chunk.page_content
            })

        with open(output_filename, "w", encoding="utf-8") as f:
            json.dump(output_data, f, ensure_ascii=False, indent=4)

        print(f"✅ Selesai! Tersimpan di '{output_filename}'.\n")

    except Exception as e:
        print(f"❌ Terjadi kesalahan pada {url}: {e}")

if __name__ == "__main__":
    # URL Uji Coba Berdasarkan Data Kamu
    urls_to_test = {
        "https://rsaulia.com/profil/": "cek_profil.json",
        "https://rsaulia.com/rawat-jalan/": "cek_rawat_jalan.json",
        "https://rsaulia.com/tata-tertib/": "cek_tata_tertib.json"
    }
    
    for url, filename in urls_to_test.items():
        scrape_rsu_aulia(url, filename)