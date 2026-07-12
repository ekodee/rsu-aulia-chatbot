import os
from dotenv import load_dotenv
from astrapy import DataAPIClient
from google import genai
from google.genai import types

# Load variabel dari .env
load_dotenv()

ASTRA_DB_API_ENDPOINT = os.getenv("ASTRA_DB_API_ENDPOINT")
ASTRA_DB_APPLICATION_TOKEN = os.getenv("ASTRA_DB_APPLICATION_TOKEN")
ASTRA_DB_COLLECTION = os.getenv("ASTRA_DB_COLLECTION")
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")

def database_setup():
    print("1. Menginisialisasi Model Embedding Gemini (SDK Resmi)...")
    try:
        client_ai = genai.Client(api_key=GEMINI_API_KEY)
        
        result = client_ai.models.embed_content(
            model="gemini-embedding-001", 
            contents="Tes koneksi Gemini",
            config=types.EmbedContentConfig(output_dimensionality=1536)
        )
        
        vector = result.embeddings[0].values
        print(f"✅ Gemini OK! Dimensi vektor: {len(vector)}")
    except Exception as e:
        print(f"❌ Gagal inisialisasi Gemini: {e}")
        return

    print("\n2. Menghubungkan ke Astra DB...")
    try:
        client_db = DataAPIClient(ASTRA_DB_APPLICATION_TOKEN)
        db = client_db.get_database_by_api_endpoint(ASTRA_DB_API_ENDPOINT)
        print(f"✅ Terhubung ke database!")
        
        collections = db.list_collection_names()
        
        if ASTRA_DB_COLLECTION not in collections:
            print(f"\n3. Membuat koleksi '{ASTRA_DB_COLLECTION}'...")
            
            # Menggunakan format parameter dictionary (Aman untuk berbagai versi astrapy)
            db.create_collection(
                ASTRA_DB_COLLECTION,
                dimension=1536,
                metric="cosine"
            )
            print(f"✅ Koleksi '{ASTRA_DB_COLLECTION}' berhasil dibuat!")
        else:
            print(f"\n✅ Koleksi '{ASTRA_DB_COLLECTION}' sudah ada, siap digunakan!")

    except Exception as e:
        # Jika cara pertama gagal, otomatis coba cara fallback
        print(f"⚠️ Mencoba metode pembuatan koleksi alternatif...")
        if ASTRA_DB_COLLECTION not in collections:
            print(f"\n3. Membuat koleksi '{ASTRA_DB_COLLECTION}'...")
            try:
                db.create_collection(
                    ASTRA_DB_COLLECTION,
                    definition={"vector": {"dimension": 1536, "metric": "cosine"}}
                )
                print(f"✅ Koleksi '{ASTRA_DB_COLLECTION}' berhasil dibuat!")
            except Exception as e:
                print(f"❌ Gagal menghubungi Astra DB: {e}")
        else:
            print(f"\n✅ Koleksi '{ASTRA_DB_COLLECTION}' sudah ada, siap digunakan!")

if __name__ == "__main__":
    database_setup()