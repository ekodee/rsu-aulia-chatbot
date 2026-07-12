import os
import asyncio
import pandas as pd
import requests
from dotenv import load_dotenv

# 1. Import library OpenAI asli
from openai import AsyncOpenAI

# 2. Import 6 Pilar Metrik Ragas 
from ragas.metrics.collections import (
    Faithfulness, 
    AnswerRelevancy, 
    ContextRecall, 
    ContextPrecision, 
    ContextEntityRecall, 
    NoiseSensitivity
)
from ragas.llms import llm_factory
from ragas.embeddings.base import embedding_factory

load_dotenv()

async def run_evaluation():
    print("\n" + "="*80)
    print("MEMULAI PROSES EVALUASI RAGAS")
    print("="*80)
    
    # 3. Setup AsyncOpenAI Client mengarah ke Aivene
    aivene_client = AsyncOpenAI(
        api_key=os.getenv("AIVENE_API_KEY"),
        base_url="https://api.aivene.com/v1"
    )

    print("[1/5] Inisialisasi AI (LLM & Embeddings)...")
    
    # Menambahkan max_tokens untuk mencegah error pada dokumen panjang
    evaluator_llm = llm_factory(
        "gemini-2.5-flash", 
        client=aivene_client,
        max_tokens=4096 
    )
    evaluator_embeddings = embedding_factory(
        "openai", 
        model="gemini-embedding-2", 
        client=aivene_client
    )
    
    # Inisialisasi 6 Objek Metrik secara eksplisit
    scorer_faithfulness = Faithfulness(llm=evaluator_llm)
    scorer_relevancy = AnswerRelevancy(llm=evaluator_llm, embeddings=evaluator_embeddings)
    scorer_recall = ContextRecall(llm=evaluator_llm)
    scorer_precision = ContextPrecision(llm=evaluator_llm)
    scorer_entity_recall = ContextEntityRecall(llm=evaluator_llm)
    scorer_noise = NoiseSensitivity(llm=evaluator_llm)

    print("[2/5] Membaca dataset Excel...")
    try:
        # HANYA MEMBACA 3 DATA TERATAS UNTUK TESTING
        df = pd.read_excel("dataset_uji.xlsx")
        print(f"      Berhasil memuat {len(df)} skenario pengujian.")
    except Exception as e:
        print(f"  Gagal membaca file Excel: {e}")
        return

    # List untuk menyimpan hasil ke tabel
    answers = []
    contexts = []
    faith_scores = []
    relevancy_scores = []
    recall_scores = []
    precision_scores = []
    entity_recall_scores = []
    noise_scores = []

    print("\n[3/5] Memulai Pengujian & Penilaian...")
    
    # Buka file log untuk Traceability Skripsi
    # nama file
    with open("bukti_evaluasi_sistem_mmr.txt", "w", encoding="utf-8") as f:
        f.write("="*85 + "\n")
        f.write("LOG EVALUASI RAGAS (6 METRICS COMPREHENSIVE)\n")
        f.write("Metrik Pengujian : Faithfulness, Answer Relevancy, Context Recall, Context Precision, Context Entity Recall, Noise Sensitivity\n")
        f.write("="*85 + "\n\n")

        for index, row in df.iterrows():
            question = row["user_input"]
            reference = row["reference"]
            print(f"\n>>> [{index+1}/{len(df)}] Menguji Pertanyaan: '{question}'")
            
            # --- Langkah A: Minta Jawaban ke Peladen ---
            try:
                response = requests.post(
                    "http://127.0.0.1:8000/api/chat/eval",
                    json={"question": question, "chat_history": []},
                    timeout=30 
                )
                
                if response.status_code == 200:
                    data = response.json()
                    answer = data["answer"]
                    ctxs = data["contexts"]
                else:
                    answer = "ERROR"
                    ctxs = ["ERROR"]
                    
            except Exception as e:
                print(f"       Koneksi error: {e}")
                answer = "ERROR"
                ctxs = ["ERROR"]
            
            answers.append(answer)
            contexts.append(ctxs)

            # --- Langkah B: Penilaian Juri Ragas (6 Metrik) ---
            if answer != "ERROR":
                print("       Menilai 6 metrik dengan AI...")
                try:
                    # 1. Menilai Faithfulness
                    res_faith = await scorer_faithfulness.ascore(
                        user_input=question, response=answer, retrieved_contexts=ctxs
                    )
                    val_faith = res_faith.value if hasattr(res_faith, 'value') else float(res_faith)
                    
                    # 2. Menilai Answer Relevancy
                    res_rel = await scorer_relevancy.ascore(
                        user_input=question, response=answer
                    )
                    val_rel = res_rel.value if hasattr(res_rel, 'value') else float(res_rel)
                    
                    # 3. Menilai Context Recall
                    res_recall = await scorer_recall.ascore(
                        user_input=question, retrieved_contexts=ctxs, reference=reference
                    )
                    val_recall = res_recall.value if hasattr(res_recall, 'value') else float(res_recall)

                    # 4. Menilai Context Precision
                    res_prec = await scorer_precision.ascore(
                        user_input=question, reference=reference, retrieved_contexts=ctxs
                    )
                    val_prec = res_prec.value if hasattr(res_prec, 'value') else float(res_prec)

                    # 5. Menilai Context Entity Recall
                    res_ent = await scorer_entity_recall.ascore(
                        reference=reference, retrieved_contexts=ctxs
                    )
                    val_ent = res_ent.value if hasattr(res_ent, 'value') else float(res_ent)

                    # 6. Menilai Noise Sensitivity
                    res_noise = await scorer_noise.ascore(
                        user_input=question, response=answer, reference=reference, retrieved_contexts=ctxs
                    )
                    val_noise = res_noise.value if hasattr(res_noise, 'value') else float(res_noise)

                except Exception as e:
                    print(f"       Error saat menilai: {e}")
                    val_faith, val_rel, val_recall, val_prec, val_ent, val_noise = 0.0, 0.0, 0.0, 0.0, 0.0, 0.0
            else:
                val_faith, val_rel, val_recall, val_prec, val_ent, val_noise = 0.0, 0.0, 0.0, 0.0, 0.0, 0.0
            
            faith_scores.append(val_faith)
            relevancy_scores.append(val_rel)
            recall_scores.append(val_recall)
            precision_scores.append(val_prec)
            entity_recall_scores.append(val_ent)
            noise_scores.append(val_noise)

            print(f"      Skor -> Faith: {val_faith:.2f} | Rel: {val_rel:.2f} | Recall: {val_recall:.2f} | Prec: {val_prec:.2f} | Ent: {val_ent:.2f} | Noise: {val_noise:.2f}")

            # --- Langkah C: Tulis ke Log Bukti ---
            f.write(f"[{index+1}] SKENARIO UJI KE-{index+1}\n")
            f.write("-" * 85 + "\n")
            f.write(f"Q  (Pertanyaan) : {question}\n")
            f.write(f"GT (Referensi)  : {reference}\n")
            f.write(f"A  (Jawaban)    : {answer}\n\n")
            
            f.write(f">>> DOKUMEN KONTEKS YANG DITARIK DARI ASTRA DB (Total: {len(ctxs)} dokumen) <<<\n")
            for i, ctx in enumerate(ctxs):
                f.write(f"\n--- Dokumen {i+1} ---\n{str(ctx).strip()}\n")
            
            f.write(f"\n>>> SKOR EVALUASI <<<\n")
            f.write(f"1. Faithfulness          : {val_faith:.4f}\n")
            f.write(f"2. Answer Relevancy      : {val_rel:.4f}\n")
            f.write(f"3. Context Recall        : {val_recall:.4f}\n")
            f.write(f"4. Context Precision     : {val_prec:.4f}\n")
            f.write(f"5. Context Entity Recall : {val_ent:.4f}\n")
            f.write(f"6. Noise Sensitivity     : {val_noise:.4f}\n")
            f.write("=" * 85 + "\n\n\n")
            await asyncio.sleep(3)

    # Masukkan hasil ke DataFrame
    df["response"] = answers
    df["retrieved_contexts"] = contexts
    df["faithfulness"] = faith_scores
    df["answer_relevancy"] = relevancy_scores
    df["context_recall"] = recall_scores
    df["context_precision"] = precision_scores
    df["context_entity_recall"] = entity_recall_scores
    df["noise_sensitivity"] = noise_scores

    print("\n[4/5] Menghitung Rata-Rata Keseluruhan...")
    def calc_avg(scores): return sum(scores) / len(scores) if scores else 0
    
    print(f"      Rata-rata Faithfulness        : {calc_avg(faith_scores):.4f}")
    print(f"      Rata-rata Answer Relevancy    : {calc_avg(relevancy_scores):.4f}")
    print(f"      Rata-rata Context Recall      : {calc_avg(recall_scores):.4f}")
    print(f"      Rata-rata Context Precision   : {calc_avg(precision_scores):.4f}")
    print(f"      Rata-rata Context Ent. Recall : {calc_avg(entity_recall_scores):.4f}")
    print(f"      Rata-rata Noise Sensitivity   : {calc_avg(noise_scores):.4f}")

    print("\n[5/5] Menyimpan laporan detail ke format Excel...")

    # nama file
    df.to_excel("hasil_evaluasi_ragas_mmr.xlsx", index=False)

    print("Proses selesai! Silakan periksa 'hasil_evaluasi_ragas_enam_metrik.xlsx' dan 'bukti_evaluasi_sistem_enam_metrik.txt'")

if __name__ == "__main__":
    # Menjalankan fungsi async utama
    asyncio.run(run_evaluation())