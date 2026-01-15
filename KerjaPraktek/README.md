# Courseva - Platform Pembelajaran Online

Platform pembelajaran online lengkap dengan sistem manajemen course, exam, dan sertifikat.

## Fitur Utama

### 1. Peserta
- Registrasi dan Login
- Browse dan daftar course
- Upload bukti pembayaran
- Belajar course dengan modul (video, PDF, text)
- Ujian dengan timer
- Download sertifikat setelah lulus

### 2. Pengajar
- Dashboard dengan statistik
- Buat dan kelola course
- Tambah modul (video, PDF, text)
- Buat exam dengan berbagai tipe pertanyaan
- Grading manual untuk essay questions
- Lihat submissions peserta

### 3. Admin
- Dashboard dengan statistik lengkap
- Manage users (peserta, pengajar, admin)
- Manage semua courses
- Verifikasi pembayaran
- View reports (enrollment, payment, completion rate)

## Struktur Folder

```
/courseva/
├── /assets/
│   ├── /css/
│   │   └── style.css
│   └── /js/
│       ├── main.js
│       └── validation.js
├── /config/
│   └── database.php
├── /includes/
│   ├── header.php
│   ├── footer.php
│   ├── functions.php
│   └── session_check.php
├── /peserta/
│   ├── dashboard.php
│   ├── courses.php
│   ├── enroll.php
│   ├── payments.php
│   ├── learn.php
│   ├── exam.php
│   ├── exam_result.php
│   └── certificates.php
├── /pengajar/
│   ├── dashboard.php
│   ├── courses.php
│   ├── create_course.php
│   ├── edit_course.php
│   ├── add_module.php
│   ├── create_exam.php
│   ├── manage_questions.php
│   └── submissions.php
├── /admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── create_user.php
│   ├── edit_user.php
│   ├── courses.php
│   ├── create_course.php
│   ├── edit_course.php
│   ├── payments.php
│   └── reports.php
├── /uploads/
│   ├── /course_thumbnails/
│   ├── /module_files/
│   ├── /bukti_pembayaran/
│   └── /certificates/
├── index.php
├── login.php
├── register.php
├── logout.php
└── README.md
```

## Instalasi

1. **Setup Database**
   - Buat database MySQL dengan nama `courseva`
   - Import file SQL (buat struktur tabel sesuai kebutuhan)
   - Update konfigurasi di `config/database.php`

2. **Konfigurasi**
   - Edit `config/database.php` dengan kredensial database Anda
   - Pastikan folder `uploads/` memiliki permission write

3. **Struktur Database yang Diperlukan**
   - `users` (id, nama_lengkap, instansi, email, username, password, nomor_hp, role, status, created_at, updated_at)
   - `courses` (id, pengajar_id, judul, deskripsi, thumbnail, durasi, harga, kategori, prasyarat_course_id, status, created_at, updated_at)
   - `modules` (id, course_id, judul, deskripsi, tipe_konten, konten, file_path, durasi, urutan, created_at, updated_at)
   - `enrollments` (id, user_id, course_id, status, created_at)
   - `payments` (id, enrollment_id, amount, bukti_pembayaran, status, notes, created_at, verified_at)
   - `module_progress` (id, user_id, course_id, module_id, status, created_at, updated_at)
   - `exams` (id, course_id, judul, deskripsi, durasi, passing_score, max_attempts, created_at)
   - `exam_questions` (id, exam_id, pertanyaan, tipe, opsi_jawaban, jawaban_benar, poin, urutan, created_at, updated_at)
   - `exam_attempts` (id, user_id, exam_id, status, score, started_at, expires_at, submitted_at)
   - `exam_answers` (id, attempt_id, question_id, jawaban, poin_diperoleh, created_at)
   - `certificates` (id, user_id, course_id, certificate_number, file_path, issued_at)

## Teknologi yang Digunakan

- PHP 7.4+
- MySQL
- Bootstrap 5.3
- JavaScript (Vanilla)
- HTML5 & CSS3

## Security Features

- Password hashing dengan `password_hash()`
- Prepared statements untuk mencegah SQL injection
- CSRF token protection
- Session management
- File upload validation
- Input sanitization

## Catatan Penting

1. Pastikan folder `uploads/` dan subfoldernya memiliki permission write
2. Untuk production, pastikan:
   - Error reporting dimatikan
   - Database credentials aman
   - HTTPS enabled
   - File upload size limits disesuaikan

## License

Proyek ini dibuat untuk keperluan Kerja Praktek.

