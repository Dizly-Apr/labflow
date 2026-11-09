# LabFlow v2 — SMK Bina Informatika

Manajemen laboratorium & stock opname (PHP + MySQL/MariaDB, cocok untuk XAMPP/Laragon).

## Update v3 — perbaikan error & Laporan User

**Perbaikan:** error `Illegal mix of collations` (1267) yang merusak halaman denah lab (`lab.php`) dan Laporan & Export. View database kini mengunci collation `utf8mb4_unicode_ci` dan tidak lagi memakai `DEFINER=root`.

**Fitur baru — Laporan User → Koordinator & Admin:**
- Role **User** (yang mengakses lab) bisa *Kirim Laporan* dari menu **Laporan Saya**, halaman lab, atau halaman PC. Jenis: kerusakan, fasilitas, jaringan, kebersihan, keamanan, saran, lainnya.
- **Koordinator & Admin** menerima di menu **Laporan Masuk** (badge, lonceng, dan kartu di Dashboard). Status: Baru → Dibaca (otomatis saat dibuka) → Ditindaklanjuti → Selesai, dengan tanggapan untuk pelapor.
- Laporan yang menyebut PC bisa langsung **dijadikan Tiket Perbaikan**. Admin bisa menghapus laporan.
- User melihat status & tanggapan di Laporan Saya (ada penanda pembaruan).

**Cara pasang:**
- Instalasi baru: import `database/labflow.sql`.
- Database yang sudah berjalan (struktur v2): backup, lalu import `database/migration_v3_laporan_user.sql` — sekali saja, aman diulang. *(File `migration_v2.sql` tidak ikut di paket ini; untuk database v1 gunakan paket v2 asli lebih dulu.)*

## Instalasi
**Database baru:** import `database/labflow.sql` (phpMyAdmin → Import).
**Database lama (v1):** backup dulu, lalu import **`database/migration_v2.sql`** ke database `labflow` — *sekali saja*, data lama tetap aman.

Login awal: `admin` / `admin123` (Admin) dan `sapdal` / `admin123` (Koordinator). **Ganti password setelah login pertama.**
Koneksi DB: `config/koneksi.php` atau env `LABFLOW_DB_HOST / _NAME / _USER / _PASS`. Zona waktu default `Asia/Jakarta` (env `LABFLOW_TZ`).

Folder `uploads/` dan `backups/` harus bisa ditulis oleh web server.

## Yang baru di v2
| Fitur | Di mana |
|---|---|
| Export laporan **PDF / Excel**: rekap kerusakan per lab, stok bulanan, audit log | menu *Laporan & Export* |
| **Tiket perbaikan** Open → Diproses → Menunggu Sparepart → Selesai, teknisi, prioritas, timeline | menu *Tiket Perbaikan* |
| Riwayat tiap PC (tiket + penggantian komponen) dan **aset & garansi** (no. inventaris, tgl beli, garansi) | halaman PC |
| **Stok minimum** per barang + peringatan "stok menipis" di dashboard, lonceng, dan menu | *Stock Opname* |
| **10 jenis komponen** (CPU, RAM, GPU, Storage, Motherboard, PSU, Monitor, Keyboard, Mouse, Jaringan) | halaman PC → *Tambah Komponen* |
| **QR per PC** + form lapor publik tanpa login | *Lab → Stiker QR*, `lapor.php` |
| **Analitik**: lab & komponen paling sering rusak, rata-rata waktu perbaikan, tren bulanan | menu *Analitik* |
| Pencarian global (mis. "VGA Missing" di semua lab) | kotak cari di topbar, `search.php` |
| Backup database (tombol + skrip terjadwal) | menu *Backup Database* (admin) |
| Dark mode & tampilan HP | tombol bulan/matahari di topbar |
| Tambah lab baru dari UI | *Data Lab → Tambah Lab* |

*(Peminjaman & jadwal lab sengaja tidak dibuat, sesuai permintaan.)*

## Perubahan teknis
- **Satu route:** `pc.php?lab=rpl&no=7` dan `lab.php?lab=rpl` menggantikan 8 folder `Lab_x/` (± 240 file `PC_n.php`). Menambah lab tidak perlu membuat file. Alamat lama (`Lab_rpl/HardwareComponents/PC_7.php`) dialihkan otomatis oleh `.htaccess`.
- **CSRF:** semua POST wajib token (`X-CSRF-Token` untuk fetch, field `csrf_token` untuk form) — dicek terpusat di `config/auth.php`.
- **Rate limiting:** login maks. 5 gagal per akun+IP dan 15 per IP / 15 menit (HTTP 429); form publik maks. 5 laporan / 10 menit / IP. Tabel `rate_limits`.
- Cookie sesi `HttpOnly` + `SameSite=Lax` (+ `Secure` di HTTPS).
- Perbaikan collation view yang menyebabkan error *Illegal mix of collations* di sebagian MariaDB.
- Barang masuk untuk barang yang sudah ada tidak lagi menimpa deskripsi lama dengan kosong.

## QR code & jaringan sekolah
QR membuka `lapor.php` di alamat server. Agar HP siswa bisa membukanya, di halaman **Stiker QR** isi *Alamat aplikasi* dengan IP/domain server (mis. `http://192.168.1.10/labflow/`), bukan `localhost`. Bisa juga dipaksa lewat env `LABFLOW_BASE_URL`. Laporan publik masuk sebagai tiket **Open**; status komponen berubah menjadi ISSUE setelah petugas memprosesnya (mencegah spam).

## Backup otomatis
```
Linux (cron):    0 2 * * *  php /path/labflow/cli/backup.php
Windows (XAMPP): C:\xampp\php\php.exe C:\xampp\htdocs\labflow\cli\backup.php   (Task Scheduler, harian)
```
Server menyimpan 30 backup terbaru di `backups/` (dilindungi `.htaccess`; unduh hanya lewat menu admin). Pulihkan lewat phpMyAdmin → Import. Folder `uploads/` disalin terpisah.

## Catatan
- Ikon (Lucide) dan grafik (Chart.js) dimuat dari CDN seperti sebelumnya, jadi butuh internet; pustaka QR (`assets/js/vendor/qrcode.js`) dan PDF (`lib/fpdf`) sudah disertakan dan bekerja offline.
- Export Excel butuh ekstensi PHP `zip` (aktif di XAMPP).
- Pustaka pihak ketiga: FPDF 1.9 (lisensi bebas pakai, `lib/fpdf/license.txt`), qrcode-generator (MIT).
