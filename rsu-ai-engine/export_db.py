import os
import json
from dotenv import load_dotenv
from astrapy import DataAPIClient

# Load environment variables dari file .env milikmu
load_dotenv()

def export_knowledge_base():
    print("Menghubungkan ke Astra DB...")
    client = DataAPIClient(os.getenv("ASTRA_DB_APPLICATION_TOKEN"))
    db = client.get_database_by_api_endpoint(os.getenv("ASTRA_DB_API_ENDPOINT"))
    collection = db.get_collection(os.getenv("ASTRA_DB_COLLECTION"))

    print("Sedang menarik data (fetching)...")
    # Mengambil seluruh dokumen di dalam collection (kosongkan parameter find)
    cursor = collection.find({})
    
    data_ekspor = []
    for doc in cursor:
        # Kita ambil bagian teks (content) dan metadatanya saja agar file tidak terlalu berat
        # (Vektor angka kita abaikan karena tidak bisa dibaca manusia)
        data_ekspor.append({
            "id": doc.get("_id"),
            "teks_konten": doc.get("content", ""),
            "sumber_metadata": doc.get("metadata", {})
        })

    # Menyimpan data ke file JSON lokal
    nama_file = "ekspor_knowledge_rsu_full.json"
    with open(nama_file, 'w', encoding='utf-8') as f:
        json.dump(data_ekspor, f, ensure_ascii=False, indent=4)
        
    print(f"✅ Berhasil! {len(data_ekspor)} dokumen telah diekspor ke file {nama_file}")

if __name__ == "__main__":
    export_knowledge_base()