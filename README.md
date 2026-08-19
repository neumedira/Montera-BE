# Montera Backend API

API Service untuk Montera, sebuah sistem manajemen pesanan (E-POS) yang mengelola menu, meja, dan transaksi. Dikembangkan menggunakan Laravel.

## Tech Stack

- **Framework:** Laravel
- **Database:** MySQL
- **Development Environment:** Laragon

## Core Modules

- **Menu Management:** Pengelolaan kategori menu, item, _addons_, dan paket (_bundles_).
- **Order & Table Management:** Proses _checkout_ pesanan pelanggan, manajemen status pesanan, dan penempatan meja.
- **Settings & Profiling:** Konfigurasi pajak (_tax_), pembayaran, struk (_receipt_), dan profil bisnis.

## Setup & Installation

1. Clone repository ini.
2. Jalankan perintah `composer install` untuk menginstal dependensi.
3. Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database (Laragon).
4. Jalankan `php artisan key:generate`.
5. Jalankan `php artisan migrate` untuk membangun skema database.
6. Jalankan `php artisan serve` untuk memulai _local server_.

---

_Kerjain woi, jangan malas!_
