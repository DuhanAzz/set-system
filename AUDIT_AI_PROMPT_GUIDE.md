# PANDUAN AUDIT ARSITEKTUR & BASIS DATA: SET SYSTEM (MULTI-SPORT MANAGEMENT)

Dokumen ini disusun sebagai panduan konteks bagi AI (Large Language Model) untuk menganalisis basis kode **SET SYSTEM** dalam rangka penulisan artikel ilmiah/teknis:
> *"Dari Regulasi Kompetisi Olahraga ke Arsitektur Perangkat Lunak: Pendekatan Berbasis Keterlacakan untuk Rekayasa Kebutuhan Domain pada Sistem Manajemen Multi-Cabang."*

---

## 1. Ikhtisar Sistem & Arsitektur

- **Nama Sistem**: SET SYSTEM (Sports Event & Tournament Management System)
- **Teknologi Utama**: PHP (Native MVC Architecture), MariaDB/MySQL (PDO), Vanilla JS, CSS/Bootstrap.
- **Pola Arsitektur**:
  - **Front Controller & Modular Router**: `public/index.php` menangani pemetaan URL modular (`/core/`, `/swim/`, `/roll/`, `/home/`, `/tools/`).
  - **Multi-Cabang (Multi-Tenant Domain)**:
    - `app/Core/`: Abstraksi inti (`Controller`, `Database`, `UploadService`, manajemen aset universal).
    - `app/Swim/`: Modul Cabang Olahraga Renang / Akuatik (mengacu pada regulasi World Aquatics / FINA / Akuatik Indonesia).
    - `app/Roll/`: Modul Cabang Olahraga Sepatu Roda / Inline Speed Skating (mengacu pada regulasi World Skate / Perserosi).
  - **Role-Based Access Control (RBAC)**:
    - Universal Admin / Superadmin (`universal_admins`)
    - Master Cabang Olahraga (`swim_users` / `roll_users` role: `master`)
    - Administrator Event / Penyelenggara (`admin`)
    - Official Klub / Manajer Kontingen (`user`)
    - Publik / Penonton (`public` / tanpa login untuk live results, jadwal, dan buku acara).

---

## 2. Struktur Berkas Kunci dalam Paket Ini

1. **Skema Basis Data (`database/schema_structure.sql`)**:
   - Skema DDL murni (48 tabel) tanpa beban baris data INSERT, mencakup entitas universal, renang, dan sepatu roda.
2. **Kontroler & Domain Logic (`app/`)**:
   - `app/Core/Database.php`: Singleton connection PDO.
   - `app/Swim/Controllers/`:
     - `SeedingController.php`: Algoritma seeding lintasan renang (spearhead/central lane assignment), pembagian seri minimal 3 perenang per heat (World Aquatics Rule SW 3.1).
     - `SwimmersController.php`: Kalkulasi Kelompok Umur (KU) dinamis on-the-fly (per 31 Desember vs hari-H) dan standardisasi UID atlet.
     - `ResultsController.php` & `LiveResultController.php`: Pencatatan hasil lomba, diskualifikasi (DQ) terikat pasal regulasi.
     - `RelayController.php` & `RelayRegistrationController.php`: Manajemen estafet tim.
   - `app/Roll/Controllers/`:
     - `Admin/RollPelotonController.php`: Mekanisme pembagian seri (Sprint Heats vs Mass Start/Starting List), batas lintasan (`max_lanes`), algoritma acak vs distribusif (pencegahan tabrakan atlet satu klub di babak awal).
     - `Admin/RollResultController.php`: Input hasil bertahap, akumulasi poin (PTP), degradasi ranking bottom-up (Eliminasi), dan rekonsiliasi hasil relay per tim.
     - `Admin/RollBibController.php`: Alokasi nomor BIB peserta.
     - `User/RollTokenRegistrationController.php`: Mekanisme kuota pendaftaran berbasis token.
3. **Penyajian Data & Pelaporan (`views/`)**:
   - Menghasilkan Buku Acara, Startlist, Hasil Cetak Resmi, dan Export CSV/PDF.
4. **Relasi Modul & Community Detection (`.graphify/GRAPH_REPORT.md`)**:
   - Laporan pemetaan god nodes, edge calls, dan kohesi modul sistem.

---

## 3. Instruksi Eksekusi Audit AI

Jika Anda adalah AI yang menerima berkas ini, lakukan audit menyeluruh terhadap 5 poin berikut:
1. **Struktur dan Entitas Domain Nyata**: Uraikan entitas apa saja yang benar-benar ada pada database dan kode, serta bagaimana pemisahan antar cabang olahraga (Swim vs Roll vs Universal) diimplementasikan.
2. **Implementasi Aturan Bisnis dan Validasi**: Tunjukkan bukti konkret baris kode/metode yang mengonversi aturan regulasi resmi (FINA/World Skate) ke dalam algoritma perangkat lunak.
3. **Kesenjangan (Traceability Gaps)**: Identifikasi perbedaan/kesenjangan antara regulasi kompetisi ideal, skema database, dan implementasi kode aktual (misalnya penanganan Tie-break, DQ reference seeding, audit log, dsb).
4. **Validitas Bukti untuk Artikel Ilmiah**: Evaluasi mana bukti empiris yang sudah sangat kuat (dapat langsung dijadikan cuplikan/tabel artikel) dan mana aspek yang masih memerlukan pengujian empiris tambahan (misal simulasi beban concurrency atau edge case tie breaker).
5. **Rekomendasi Publikasi**: Berikan saran penajaman judul artikel, rumusan kontribusi ilmiah (*novelty*), dan metode penelitian rekayasa perangkat lunak (misal: *Domain-Driven Design Traceability*, *Empirical Software Architecture Audit*).
