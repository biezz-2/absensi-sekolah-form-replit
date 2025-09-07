# Kehadiran Siswa - Monorepo

## Menjalankan
- Prasyarat: Docker & Docker Compose terpasang.
- Jalankan:

	 docker compose up --build

Akses:
- Frontend: http://localhost:8080
- Backend (proxied): http://localhost:8080/api
- Mailhog: http://localhost:8025
- Soketi metrics: http://localhost:9601

## Struktur
- backend: Laravel API (dibuat otomatis saat pertama run)
- frontend: Next.js App (dibuat otomatis saat pertama run)
- docker/nginx: konfigurasi Nginx reverse proxy

## ENV
- Salin `backend/.env.example` ke `backend/.env` setelah Laravel terbuat.
- Salin `frontend/.env.example` ke `frontend/.env`.

## Catatan
- Kamera & Geolocation membutuhkan HTTPS untuk produksi.
- Gantilah kredensial default sebelum deploy.

