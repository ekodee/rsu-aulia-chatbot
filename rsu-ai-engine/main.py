import os
import re
import datetime
import requests
import io
import PyPDF2
import asyncio
from bs4 import BeautifulSoup
from fastapi import FastAPI, HTTPException, UploadFile, File
from fastapi.responses import StreamingResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from dotenv import load_dotenv
from typing import List, Dict, Any
from astrapy import DataAPIClient

os.environ["USER_AGENT"] = "RSU-Aulia-Chatbot/1.0"

# LangChain components
from langchain_core.embeddings import Embeddings
from langchain_astradb import AstraDBVectorStore
from langchain_core.documents import Document
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_core.messages import HumanMessage, AIMessage
from langchain_core.output_parsers import StrOutputParser
from langchain_openai import ChatOpenAI

# Load environment variables
load_dotenv()

app = FastAPI(title="RSU Aulia AI Engine", description="Microservice RAG untuk Chatbot RS")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:3000"], 
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- CUSTOM EMBEDDING CLASS ---
class GeminiCustomEmbeddings(Embeddings):
    def __init__(self, api_key: str):
        self.api_key = api_key
        self.url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent?key={self.api_key}"
        
    def embed_documents(self, texts: List[str]) -> List[List[float]]:
        embeddings = []
        for text in texts:
            payload = {
                "content": {"parts": [{"text": text}]},
                "outputDimensionality": 1536
            }
            response = requests.post(self.url, json=payload)
            if response.status_code != 200:
                raise Exception(f"Google API Error: {response.status_code} - {response.text}")
            data = response.json()
            embeddings.append(data["embedding"]["values"])
        
        # ========== HASIL EMBEDDING ==========
        print("\n" + "="*70)
        print("HASIL EMBEDDING (KONVERSI TEKS KE VEKTOR)")
        print("="*70)
        print(f"Model Embedding yang digunakan: gemini-embedding-001 (REST API)")
        print(f"Dimensi Vektor: 1536")
        print(f"Total Chunk yang diproses: {len(texts)}")
        if embeddings:
            print(f"Preview nilai vektor pertama (15 dimensi awal):")
            print(f"{embeddings[0][:15]} ... [dst]")
        print("="*70 + "\n")
        # =========================================================
        return embeddings

    def embed_query(self, text: str) -> List[float]:
        return self.embed_documents([text])[0]

    async def aembed_documents(self, texts: List[str]) -> List[List[float]]:
        return await asyncio.to_thread(self.embed_documents, texts)

    async def aembed_query(self, text: str) -> List[float]:
        embedding = await asyncio.to_thread(self.embed_query, text)
        return embedding

# --- 1. KONFIGURASI AI & DATABASE ---
print("Mempersiapkan AI dan Database...")
try:
    embeddings = GeminiCustomEmbeddings(api_key=os.getenv("GEMINI_API_KEY"))
    vector_store = AstraDBVectorStore(
        collection_name=os.getenv("ASTRA_DB_COLLECTION"),
        embedding=embeddings,
        api_endpoint=os.getenv("ASTRA_DB_API_ENDPOINT"),
        token=os.getenv("ASTRA_DB_APPLICATION_TOKEN"),
        namespace=os.getenv("ASTRA_DB_NAMESPACE")
    )
    print("✅ Sistem AI dan Vector Store siap!")
except Exception as e:
    print(f"❌ Gagal inisialisasi: {e}")

# --- 2A. ENDPOINT INGESTION ARTIKEL (WEBSITE) ---
class IngestRequest(BaseModel):
    url: str

@app.post("/api/ingest/url")
async def ingest_from_url(request: IngestRequest):
    try:
        print(f"\n[INGESTION] Sedang memproses URL: {request.url}")
        response = requests.get(request.url, headers={"User-Agent": "Mozilla/5.0"})
        soup = BeautifulSoup(response.content, "html.parser")
        
        # ========== HASIL EKSTRAKSI WEBSITE ==========
        print("\n" + "="*70)
        print("HASIL EKSTRAKSI INFORMASI (WEBSITE)")
        print("="*70)
        print(f"URL yang diproses: {request.url}")
        print(f"Status HTTP Response: {response.status_code}")
        # =================================================================
        
        for element in soup(["nav", "script", "style", "noscript", "meta"]):
            element.decompose()
            
        noise_selectors = [
            {"id": "header"}, {"class_": "wpbf-page-header"}, 
            {"class_": "wpbf-page-footer"}, {"data-elementor-type": "header"}, 
            {"data-elementor-type": "footer"}, {"class_": "elementor-location-header"},
            {"class_": "elementor-location-footer"}
        ]
        for selector in noise_selectors:
            for noise in soup.find_all(**selector):
                noise.decompose()

        target_text = soup.find(string=re.compile("Ikuti Kami", re.IGNORECASE))
        if target_text:
            section_to_destroy = target_text.find_parent("section", class_="elementor-top-section")
            if section_to_destroy:
                section_to_destroy.decompose()

        content_area = soup.find("main", id="main")
        if not content_area: content_area = soup.find("div", attrs={"data-elementor-type": "wp-page"})
        if not content_area: content_area = soup.find("div", attrs={"data-elementor-type": "wp-post"})
        if not content_area: content_area = soup.find("body")

        raw_text = content_area.get_text(separator=" ", strip=True)
        
        # ===================
        print(f"Panjang Teks Mentah (raw_text): {len(raw_text)} karakter")
        print(f"Preview Teks Mentah (150 karakter pertama):")
        print(f"{raw_text[:150]}..." if len(raw_text) > 150 else raw_text)
        # ===================================================
        
        clean_text = re.sub(r'\n+', ' ', raw_text)
        clean_text = re.sub(r'\s+', ' ', clean_text)
        
        # ========== HASIL DATA CLEANING ==========
        print("\n" + "="*70)
        print("HASIL DATA CLEANING DAN TEXT PREPROCESSING")
        print("="*70)
        print(f"Panjang teks SEBELUM cleaning: {len(raw_text)} karakter")
        # ==============================================================
        
        footer_keywords = [
            "Penghargaan & Akreditasi RSU Aulia", 
            "Lokasi Kami: RSU Aulia",             
            "Melayani lebih dari 40 tahun",       
            "Ikuti Kami Facebook",                
            "© 2026 - RSU Aulia"                  
        ]
        for keyword in footer_keywords:
            if keyword in clean_text:
                clean_text = clean_text.split(keyword)[0]
                
        clean_text = clean_text.strip()
        
        print(f"Panjang teks SESUDAH cleaning: {len(clean_text)} karakter")
        print(f"Persentase pengurangan: {(1 - len(clean_text)/len(raw_text))*100:.1f}%")
        print(f"\nPreview Teks Bersih (200 karakter pertama):")
        print(f"{clean_text[:200]}..." if len(clean_text) > 200 else clean_text)
        print("="*70 + "\n")
        
        docs = [Document(page_content=clean_text, metadata={"source": request.url, "type": "artikel"})]
        text_splitter = RecursiveCharacterTextSplitter(chunk_size=500, chunk_overlap=50)
        chunked_docs = text_splitter.split_documents(docs)
        
        # ========== HASIL CHUNKING ==========
        print("="*70)
        print("HASIL SEGMENTASI DOKUMEN (CHUNKING)")
        print("="*70)
        print(f"Metode Chunking: RecursiveCharacterTextSplitter")
        print(f"Ukuran Chunk (chunk_size): 500 karakter")
        print(f"Nilai Overlap (chunk_overlap): 50 karakter")
        print(f"Total Chunk yang terbentuk: {len(chunked_docs)} bagian")
        if chunked_docs:
            print(f"\nPreview Chunk Pertama:")
            print(f"  Metadata: {chunked_docs[0].metadata}")
            print(f"  Isi Teks: {chunked_docs[0].page_content[:300]}..." if len(chunked_docs[0].page_content) > 300 else f"  Isi Teks: {chunked_docs[0].page_content}")
        print("="*70 + "\n")
        # =========================================================
        
        vector_store.add_documents(chunked_docs)
        print("✅ Data ARTIKEL BERSIH berhasil disimpan ke Astra DB!")

        return {
            "status": "success", 
            "message": f"Berhasil menyimpan {len(chunked_docs)} chunks bersih dari {request.url}"
        }

    except Exception as e:
        print(f"❌ Error saat ingestion artikel: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# --- 2B. ENDPOINT INGESTION KHUSUS JADWAL DOKTER ---
class IngestJadwalRequest(BaseModel):
    url: str = "https://rsaulia.com/jadwal-praktik-dokter/"

@app.post("/api/ingest/jadwal")
async def ingest_jadwal_dokter(request: IngestJadwalRequest):
    try:
        print(f"\n[TABLE INGESTION] Sedang mengambil jadwal dari: {request.url}")
        
        response = requests.get(request.url, headers={"User-Agent": "Mozilla/5.0"})
        soup = BeautifulSoup(response.content, "html.parser")
        
        # ==========EKSTRAKSI JADWAL DOKTER ==========
        print("\n" + "="*70)
        print("HASIL EKSTRAKSI INFORMASI (JADWAL DOKTER)")
        print("="*70)
        print(f"URL Sumber: {request.url}")
        print(f"Status HTTP: {response.status_code}")
        # =================================================================
        
        hasil_kalimat = []
        accordion_headers = soup.find_all("div", class_="elementor-tab-title")
        
        print(f"Jumlah Poli yang ditemukan: {len(accordion_headers)}")
        
        for header in accordion_headers:
            poli = header.text.strip()
            content_id = header.get("aria-controls")
            if not content_id: continue
                
            content_div = soup.find("div", id=content_id)
            if not content_div: continue
                
            table = content_div.find("table")
            if not table: continue
                
            tbody = table.find("tbody")
            rows = tbody.find_all("tr") if tbody else table.find_all("tr")
            
            for row in rows:
                cols = row.find_all("td")
                if not cols: continue
                
                data_dokter = {}
                for col in cols:
                    label = col.get("data-label", "").strip()
                    val = col.text.strip()
                    if label: data_dokter[label] = val
                
                nama_dokter = data_dokter.get("Dokter", "")
                if not nama_dokter: continue
                
                jadwal_hari = []
                for hari in ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"]:
                    val = data_dokter.get(hari, "-")
                    if val and val != "-" and val.lower() != "libur":
                        jadwal_hari.append(f"{hari} jam {val}")
                
                catatan = data_dokter.get("Catatan", "-")
                catatan_teks = f" (Catatan: {catatan})" if catatan and catatan != "-" else ""
                
                if jadwal_hari:
                    jadwal_str = ", ".join(jadwal_hari)
                    kalimat = f"Jadwal Praktik {nama_dokter} di Poli {poli} adalah pada hari {jadwal_str}.{catatan_teks}"
                else:
                    kalimat = f"Jadwal Praktik {nama_dokter} di Poli {poli} saat ini belum ada jadwal atau libur."
                hasil_kalimat.append(kalimat)

        if not hasil_kalimat:
            raise Exception("Gagal menemukan data tabel dengan struktur EAEL.")

        print(f"\nTotal Data Jadwal Dokter yang berhasil diekstrak: {len(hasil_kalimat)}")
        print(f"\nContoh 2 data jadwal yang diekstrak:")
        for i, contoh in enumerate(hasil_kalimat[:2]):
            print(f"  [{i+1}] {contoh[:200]}..." if len(contoh) > 200 else f"  [{i+1}] {contoh}")
        print("="*70 + "\n")

        docs = [Document(page_content=kalimat, metadata={"source": request.url, "type": "jadwal_dokter"}) for kalimat in hasil_kalimat]
        vector_store.add_documents(docs)
        print(f"✅ {len(docs)} data Jadwal Dokter BERHASIL disimpan ke Astra DB!")

        return {
            "status": "success", 
            "message": f"Berhasil merakit dan menyimpan {len(docs)} data jadwal dokter dari {request.url}"
        }

    except Exception as e:
        print(f"❌ Error saat ingestion jadwal: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# --- 2C. ENDPOINT INGESTION KHUSUS DOKUMEN PDF ---
@app.post("/api/ingest/pdf")
async def ingest_pdf_document(file: UploadFile = File(...)):
    try:
        print(f"\n[PDF INGESTION] Sedang memproses file PDF: {file.filename}")
        
        if not file.filename.lower().endswith('.pdf'):
            raise HTTPException(status_code=400, detail="File harus berupa PDF!")

        content = await file.read()
        pdf_reader = PyPDF2.PdfReader(io.BytesIO(content))
        
        # ========== EKSTRAKSI PDF ==========
        print("\n" + "="*70)
        print("HASIL EKSTRAKSI INFORMASI (DOKUMEN PDF)")
        print("="*70)
        print(f"Nama File: {file.filename}")
        print(f"Jumlah Halaman PDF: {len(pdf_reader.pages)}")
        # =======================================================
        
        raw_text = ""
        for page_num in range(len(pdf_reader.pages)):
            page = pdf_reader.pages[page_num]
            extracted = page.extract_text()
            if extracted:
                raw_text += extracted + "\n"
        
        print(f"Panjang Teks Mentah (raw_text): {len(raw_text)} karakter")
        print(f"Preview Teks Mentah (200 karakter pertama):")
        print(f"{raw_text[:200]}..." if len(raw_text) > 200 else raw_text)
        
        clean_text = re.sub(r'\n+', '\n', raw_text).strip()
        
        # ========== DATA CLEANING PDF ==========
        print("\n" + "="*70)
        print("HASIL DATA CLEANING DAN TEXT PREPROCESSING (PDF)")
        print("="*70)
        print(f"Panjang teks SEBELUM cleaning: {len(raw_text)} karakter")
        print(f"Panjang teks SESUDAH cleaning: {len(clean_text)} karakter")
        print(f"Persentase pengurangan: {(1 - len(clean_text)/len(raw_text))*100:.1f}%")
        print(f"\nPreview Teks Bersih (200 karakter pertama):")
        print(f"{clean_text[:200]}..." if len(clean_text) > 200 else clean_text)
        print("="*70 + "\n")
        # ===========================================================
        
        if not clean_text:
            raise Exception("Tidak ada teks yang bisa diekstrak dari PDF ini. Pastikan PDF bukan berisi gambar hasil scan.")

        docs = [Document(page_content=clean_text, metadata={"source": file.filename, "type": "pdf_document"})]
        text_splitter = RecursiveCharacterTextSplitter(chunk_size=1000, chunk_overlap=100)
        chunked_docs = text_splitter.split_documents(docs)
        
        # ========== HASIL CHUNKING PDF ==========
        print("="*70)
        print("HASIL SEGMENTASI DOKUMEN (CHUNKING) - PDF")
        print("="*70)
        print(f"Metode Chunking: RecursiveCharacterTextSplitter")
        print(f"Ukuran Chunk (chunk_size): 1000 karakter")
        print(f"Nilai Overlap (chunk_overlap): 100 karakter")
        print(f"Total Chunk yang terbentuk: {len(chunked_docs)} bagian")
        if chunked_docs:
            print(f"\nPreview Chunk Pertama:")
            print(f"  Metadata: {chunked_docs[0].metadata}")
            print(f"  Isi Teks: {chunked_docs[0].page_content[:300]}..." if len(chunked_docs[0].page_content) > 300 else f"  Isi Teks: {chunked_docs[0].page_content}")
        print("="*70 + "\n")
        # =============================================================
        
        vector_store.add_documents(chunked_docs)
        print(f"✅ Dokumen PDF {file.filename} BERHASIL disimpan ke Astra DB!")

        return {
            "status": "success", 
            "message": f"Berhasil membaca PDF '{file.filename}' dan mengubahnya menjadi {len(chunked_docs)} chunks pengetahuan."
        }

    except Exception as e:
        print(f"❌ Error saat ingestion PDF: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# --- 2D. ENDPOINT DELETE KNOWLEDGE ---
class DeleteKnowledgeRequest(BaseModel):
    source: str 

@app.post("/api/knowledge/delete")
async def delete_knowledge(request: DeleteKnowledgeRequest):
    try:
        print(f"\n[DELETE] Meminta penghapusan data memori dengan source: {request.source}")
        client = DataAPIClient(os.getenv("ASTRA_DB_APPLICATION_TOKEN"))
        db = client.get_database_by_api_endpoint(os.getenv("ASTRA_DB_API_ENDPOINT"))
        collection = db.get_collection(os.getenv("ASTRA_DB_COLLECTION"))
        result = collection.delete_many({"metadata.source": request.source})
        deleted_count = result.deleted_count
        print(f"✅ Berhasil menghapus {deleted_count} potongan memori dari Astra DB!")
        return {
            "status": "success", 
            "message": f"Berhasil menghapus {deleted_count} potongan memori untuk sumber: {request.source}"
        }
    except Exception as e:
        print(f"❌ Error saat menghapus knowledge: {e}")
        raise HTTPException(status_code=500, detail=str(e))


# --- 3. ENDPOINT CHAT ENGINE ---
print("Mempersiapkan Chat Engine dengan Memory...")
llm = ChatOpenAI(
    model="gemini-2.5-flash",
    temperature=0.3,
    api_key=os.getenv("AIVENE_API_KEY"),
    base_url="https://api.aivene.com/v1"
)

# --- GANTI BARIS INI ---
# retriever = vector_store.as_retriever(search_type="similarity", search_kwargs={"k": 7}) 

# --- MENJADI SEPERTI INI ---
retriever = vector_store.as_retriever(
    search_type="mmr", 
    search_kwargs={
        "k": 15,          
        "fetch_k": 40,    
        "lambda_mult": 0.5 
    }
)

system_prompt = """Kamu adalah asisten virtual resmi untuk Rumah Sakit Umum Aulia (RSU Aulia).
Berikut adalah informasi identitas mutlakmu:
- Alamat: Jl. Jeruk Raya No.15, Jagakarsa, Jakarta Selatan, DKI Jakarta 12620.
- Kontak: (021) 21798462 atau WhatsApp di 0812-8011-0853.
- Gawat Darurat: 0851-5887-6890.
Waktu operasional sistem saat ini: {waktu_sekarang}. (Gunakan ini sebagai acuan pasti untuk menyapa selamat pagi/siang/sore/malam).

Gunakan potongan informasi dari database berikut untuk menjawab pertanyaan pasien:
---------------------
{context}
---------------------
Aturan:
1. Jawab dengan ramah, profesional, dan empatik.
2. Perhatikan riwayat percakapan sebelumnya (chat history) untuk memahami konteks pertanyaan pasien saat ini.
3. JIKA DAN HANYA JIKA informasi tidak ada di database atau identitasmu, arahkan pasien ke Customer Service.
Jangan mengarang jawaban (halusinasi).
"""

prompt = ChatPromptTemplate.from_messages([
    ("system", system_prompt),
    MessagesPlaceholder(variable_name="chat_history"),
    ("human", "{question}")
])

def format_docs(docs):
    print("\n" + "="*70)
    print("HASIL PENCARIAN KEMIRIPAN VEKTOR (RETRIEVAL)")
    print("="*70)
    print(f"Metode Pencarian   : Similarity Search (Cosine/Dot Product)")
    print(f"Target Jumlah (k)  : 7")
    print(f"Dokumen Ditemukan  : {len(docs)}")
    print("-" * 70)
    print("[Top 3 Dokumen Teratas]:")
    
    # Hanya tampilkan 3 dokumen teratas agar rapi di screenshot
    for i, doc in enumerate(docs[:7]):
        preview = doc.page_content
        print(f"  [{i+1}] Source : {doc.metadata.get('source', 'Unknown')}")
        print(f"      Preview: {preview}")
    
    print("="*70 + "\n")
    return "\n\n".join(doc.page_content for doc in docs)

answer_chain = prompt | llm | StrOutputParser()

class ChatRequest(BaseModel):
    question: str
    chat_history: List[Dict[str, str]] = [] 

@app.post("/api/chat")
async def chat_endpoint(request: ChatRequest):
    print("\n" + "="*70)
    print("PENGAMBILAN KONTEKS & MEMORI PERCAKAPAN")
    print("="*70)
    print("Status Memori")
    if request.chat_history:
        print(f"Sistem mendeteksi {len(request.chat_history)} riwayat pesan sebelumnya:")
        for msg in request.chat_history[-2:]:
            preview_msg = msg['content'][:80].replace('\n', ' ') + "..." if len(msg['content']) > 80 else msg['content']
            print(f"  -> {msg['role'].upper()}: {preview_msg}")
    else:
        print("Sistem tidak mendeteksi riwayat pesan (Sesi percakapan baru).")

    formatted_history = []
    for msg in request.chat_history:
        if msg["role"] == "user":
            formatted_history.append(HumanMessage(content=msg["content"]))
        elif msg["role"] == "ai":
            formatted_history.append(AIMessage(content=msg["content"]))

    # FITUR ADVANCED RAG (QUERY REFORMULATION)
    standalone_query = request.question
    if formatted_history:
        reformulate_prompt = ChatPromptTemplate.from_messages([
            ("system", "Diberikan riwayat percakapan dan pertanyaan terbaru, rumuskan ulang pertanyaan tersebut menjadi pertanyaan mandiri (standalone) yang spesifik dengan mengganti kata ganti (misal 'beliau', 'dia') menjadi subjek asli (misal nama dokter/lokasi). JANGAN menjawabnya, cukup kembalikan teks pertanyaannya."),
            MessagesPlaceholder(variable_name="chat_history"),
            ("human", "{question}")
        ])
        reformulate_chain = reformulate_prompt | llm | StrOutputParser()
        standalone_query = await reformulate_chain.ainvoke({
            "chat_history": formatted_history,
            "question": request.question
        })
        
    print("\n[B. Advanced RAG: Query Reformulation]")
    print(f"Pertanyaan Asli User : '{request.question}'")
    print(f"Hasil Reformulasi AI : '{standalone_query}'")
    print("="*70 + "\n")

    waktu_realtime = datetime.datetime.now().strftime("%A, %d %B %Y pukul %H:%M WIB")

    # MENGAMBIL DOKUMEN (Memanggil format_docs untuk bukti 4.2.1)
    docs = await asyncio.to_thread(retriever.invoke, standalone_query)
    context_str = format_docs(docs)

    print("\n" + "="*70)
    print("HASIL INTEGRASI PROMPT DENGAN MODEL GEMINI")
    print("="*70)
    print("Merakit System Prompt dan Human Prompt dengan variabel berikut:")
    print(f"- Waktu Real-time  : {waktu_realtime}")
    print(f"- Pertanyaan User : '{request.question}'")
    print(f"- Status Konteks   : {len(docs)} dokumen terlampir sebagai konteks LLM")
    print(f"- Status Memori    : Disematkan dalam format array (MessagesPlaceholder)")
    print("="*70 + "\n")

    async def stream_generator():
        final_answer = ""
        try:
            async for chunk in answer_chain.astream({
                "context": context_str,
                "question": request.question,
                "chat_history": formatted_history,
                "waktu_sekarang": waktu_realtime
            }):
                final_answer += chunk
                yield f"data: {chunk}\n\n"
            
            print("\n" + "="*70)
            print("HASIL PEMBENTUKAN JAWABAN CHATBOT")
            print("="*70)
            print(final_answer)
            print("="*70 + "\n")
            
        except Exception as e:
            yield f"data: Error - {str(e)}\n\n"

    return StreamingResponse(stream_generator(), media_type="text/event-stream")


@app.post("/api/chat/eval")
async def chat_eval_endpoint(request: ChatRequest):
    waktu_realtime = datetime.datetime.now().strftime("%A, %d %B %Y pukul %H:%M WIB")
    
    # 1. Ambil dokumen relevan (Maksimal sesuai limit 'k' yang baru, misal 10)
    docs = await asyncio.to_thread(retriever.invoke, request.question)
    context_str = "\n\n".join(doc.page_content for doc in docs)
    
    # 2. Dapatkan jawaban AI sekaligus (bukan stream)
    final_answer = await answer_chain.ainvoke({
        "context": context_str,
        "question": request.question,
        "chat_history": [], # Dikosongkan karena Ragas menguji pertanyaan tunggal
        "waktu_sekarang": waktu_realtime
    })
    
    # 3. Kembalikan Jawaban DAN Konteks dalam format JSON untuk dinilai Ragas
    return {
        "answer": final_answer,
        "contexts": [doc.page_content for doc in docs] 
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)