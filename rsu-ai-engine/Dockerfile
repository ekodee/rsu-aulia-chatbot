# Menggunakan OS Linux super ringan yang sudah terinstal Python 3.12
FROM python:3.12-slim

# Mencegah Python menulis file .pyc (menghemat memori)
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Membuat folder kerja di dalam container
WORKDIR /app

# Menyalin file daftar library
COPY requirements.txt .

# Menginstal library tanpa menyimpan cache instalasi agar image tetap kecil
RUN pip install --no-cache-dir -r requirements.txt

# Menyalin seluruh kode AI Anda ke dalam container
COPY . .

# Mengekspos port 8000 agar bisa diakses
EXPOSE 8000

# Perintah wajib untuk menyalakan server Uvicorn
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]