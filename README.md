# Sistem Pendaftaran Online Anggota HIMATIK
> **Landing Page Pendaftaran Modern & Paperless + Backend PHP & Database MySQL + Panel Admin Panitia Seleksi**  
> *Himpunan Mahasiswa Teknik Informatika (HIMATIK)*

---

## 🎨 Logo Resmi & Konsep Desain
- **Logo Resmi HIMATIK**: Telah disematkan di seluruh antarmuka web, kartu bukti cetak, dan panel admin (`assets/logo-himatik.png`).
- **Skema Warna Resmi**:
  - **Electric Cyan Blue (`#00A3E0` / `#0099FF`)**: Diambil dari lingkaran luar dan pusat logo resmi HIMATIK.
  - **Charcoal Black / Deep Navy (`#090D16` / `#0F172A`)**: Khas roda gigi (*gear*) dan siluet panah hitam logo.
  - **White & Slate**: Kontras tinggi untuk kenyamanan membaca.

---

## 🚀 3 Divisi Spesialisasi HIMATIK

Sistem pendaftaran kini telah disesuaikan menjadi **3 Divisi Utama**:

1. **Pemrograman**:
   - Pengembangan Aplikasi Web (Fullstack) & Mobile App.
   - Competitive Programming, Logika Algoritma & Rekayasa Perangkat Lunak.
2. **Mikro**:
   - Mikrokontroler (Arduino, ESP32, STM32).
   - Sensor Cerdas, Otomasi & Internet of Things (IoT).
   - Rancang Bangun Hardware & Robotika.
3. **Jaringan**:
   - Routing, Switching & Topologi Jaringan (Mikrotik / Cisco).
   - Administrasi Server Linux & Arsitektur Cloud.
   - Keamanan Jaringan & Cyber Defense.

---

## 🛠️ Fitur Sistem Lengkap

### 1. Portal Calon Anggota (`index.html`)
- **Hero Section**: Value Proposition kontras (Metode Konvensional vs Sistem Online HIMATIK).
- **Interactive Mock Card**: Kartu profil pendaftar digital dengan simulasi live status.
- **Section 3 Divisi**: Menampilkan detail spesialisasi Pemrograman, Mikro, dan Jaringan.
- **Matchmaker Quiz 3 Divisi**: Kuis cerdas 2 pertanyaan untuk mencocokkan minat calon anggota ke divisi yang tepat.
- **Formulir Pendaftaran Multi-Step (3 Langkah)**:
  - Langkah 1: Data Diri (Nama, NIM, Email Kampus, No. WA, Angkatan, Kelas).
  - Langkah 2: Pilihan Divisi Utama & Cadangan (Pemrograman / Mikro / Jaringan), Motivasi, Link Portofolio.
  - Langkah 3: Unggah Pas Foto & Scan KTM/KRS aktif digital.
- **Real-Time NIM Tracker**:
  - Lacak progres 4 tahapan seleksi (Form Masuk, Verifikasi, Wawancara, Pengumuman).
  - Membaca jadwal screening dan catatan khusus panitia.
- **Cetak / Unduh Kartu Bukti Pendaftaran (`cetak-bukti.php`)**:
  - Dokumen resmi berkop logo HIMATIK siap cetak / PDF (Ctrl+P) lengkap dengan verifikasi QR digital dan pas foto.
- **Dark & Light Mode Switcher**: Dukungan tema gelap dan terang secara mulus.
- **FAQ Accordion**: Menjawab keraguan umum pendaftar.

### 2. Panel Admin Panitia Seleksi (`admin/`)
- **Sistem Keamanan Login (`admin/login.php` & `admin/logout.php`)**:
  - Melindungi data calon anggota dengan proteksi sesi panitia (Session Auth).
  - Akun Default: `admin_himatik` / `admin123`.
- **Dashboard Statistik Terpusat (`admin/index.php`)**:
  - Ringkasan total pendaftar, tahap verifikasi berkas, jadwal wawancara, dan yang telah lolos resmi.
- **Filter Berdasarkan 3 Divisi**:
  - Filter calon anggota berdasarkan divisi Pemrograman, Mikro, atau Jaringan.
- **Export Data ke Excel / CSV (`admin/export-excel.php`)**:
  - Download seluruh data calon anggota dalam format `.csv` (UTF-8 BOM) yang kompatibel dengan Microsoft Excel & Google Sheets.
- **Integrasi Direct WhatsApp Chat**:
  - Panitia dapat mengklik tombol "💬 Hubungi WA" untuk langsung membuka WhatsApp Web/Aplikasi dengan template pesan otomatis.
- **Manajemen Berkas & Update Status**:
  - Periksa foto dan KTM yang diunggah, tentukan jadwal wawancara Zoom/offline, berikan catatan khusus, dan ubah status seleksi.
  - Opsi hapus data pendaftar spam/duplikat (`admin/delete.php`).

---

## 🌐 Tautan Mengakses di Browser

- **Portal Calon Anggota**: 👉 `http://localhost/himatik-registration/`
- **Panel Admin Panitia**: 👉 `http://localhost/himatik-registration/admin/login.php`
  - Username: `admin_himatik`
  - Password: `admin123`
- **phpMyAdmin Database**: 👉 `http://localhost/phpmyadmin/` (Database: `himatik_db`)

---

## 🧪 Data Demo Pengujian (3 Divisi)

| NIM | Nama | Divisi Utama | Divisi Cadangan | Status Seleksi |
| :--- | :--- | :--- | :--- | :--- |
| `202410370311204` | Ahmad Rizky Pratama | **Pemrograman** | Jaringan | Tahap Verifikasi Berkas |
| `202410370311105` | Dewi Sekar Ayu | **Mikro** | Pemrograman | Jadwal Wawancara Siap |
| `202410370311088` | Farhan Maulana | **Jaringan** | Mikro | RESMI DITERIMA DI HIMATIK 🎉 |
