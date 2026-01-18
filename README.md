<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<h1 align="center">Depart</h1>

<p align="center">
    Aplikasi manajemen dan reservasi tiket bus berbasis web yang dibangun dengan Laravel.
</p>

## Deskripsi Singkat

Aplikasi operasional perusahaan otobus (PO). Menghubungkan pelanggan, supir, dan manajemen dalam satu lingkungan yang terintegrasi. Benefit yang dirasakan tidak hanya untuk customer saja tetapi juga untuk supir dan manajemen.

## Fitur Utama

### 👤 Portal Pelanggan
*   **Pencarian Jadwal Real-time**: Cari bus berdasarkan rute dan tanggal keberangkatan.
*   **Pemilihan Kursi Interaktif**: Pilih kursi yang diinginkan secara visual.
*   **Booking & Pembayaran**: Proses pemesanan mudah dengan upload bukti bayar.
*   **Tiket Digital & Riwayat**: Akses tiket elektronik dan riwayat perjalanan kapan saja.

### 🚌 Portal Supir
*   **Dashboard Perjalanan**: Lihat jadwal keberangkatan hari ini dan mendatang.
*   **Manifest Penumpang**: Daftar lengkap penumpang untuk setiap perjalanan.
*   **Check-in Penumpang**: Validasi kehadiran penumpang dengan mudah.
*   **Manajemen Biaya & Laporan**: Ajukan klaim pengeluaran operasional dan lihat riwayat pendapatan.

### 🏢 Portal Manajemen (Admin & Owner)
*   **Dashboard Analitik**: Ringkasan performa finansial dan operasional.
*   **Manajemen Armada & Rute**: Kelola data bus, rute perjalanan, dan jadwal.
*   **Manajemen Pengguna**: Kelola akun admin, supir, dan pelanggan.
*   **Validasi Transaksi**: Verifikasi bukti pembayaran dan status pemesanan.
*   **Laporan Keuangan**: Laporan detail pendapatan, pengeluaran, dan laba bersih.

## Teknologi

Platform & Framework pembuatan aplikasi:
*   **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
*   **Backend**: Laravel 12 (PHP 8.2+)
*   **Database**: PostgreSQL
*   **External API**: Gemini

Suggested System Requirement:
*   **PHP**: 8.4.15
*   **Database**: PostgreSQL 18
*   **Web Server**: Nginx 1.28.0
*   **Node.js**: 24.11.1

## Instalasi dan Penggunaan

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lokal:

1.  **Clone Repository**
    ```bash
    git clone https://github.com/motoric-o/depart-app.git
    cd depart-app
    ```

2.  **Install Dependensi PHP & JavaScript**
    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Environment**
    Salin file contoh konfigurasi dan buat file `.env` baru.
    ```bash
    cp .env.example .env
    ```

    #### Konfigurasi

    ```.env
    GEMINI_API_KEY= <Isi dengan API key pribadi>

    DEBUGBAR_ENABLED= <true/false> (true untuk development, false untuk production)
    ```

4.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

5.  **Migrasi & Seeding Database**
    ```bash
    php artisan migrate --seed
    ```

6.  **Jalankan Aplikasi**
    Jalan server development:
    ```bash
    npm run dev
    ```
    Dan di terminal lain:
    ```bash
    php artisan serve
    ```

Aplikasi dapat diakses di `http://localhost:8000`.

## Lisensi

[MIT license](https://opensource.org/licenses/MIT).
