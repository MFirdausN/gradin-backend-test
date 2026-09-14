# Gradin Courier

REST API master data kurir untuk programming test Gradin, dengan dashboard bonus berbahasa Indonesia. Dibangun di atas project Laravel yang sudah disiapkan, menggunakan PHP enum untuk level 1–5, pagination, pencarian multi-kata, filter level, validasi, dan soft delete.

## Menjalankan project

Kebutuhan: PHP 8.3+ (pengembangan diuji pada PHP 8.4), Composer 2, ekstensi PDO SQLite. Node.js 22.12+ dan npm hanya diperlukan untuk dashboard bonus.

```sh
git clone https://github.com/MFirdausN/gradin-backend-test.git
cd gradin-backend-test
composer install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan db:seed --class=CourierSeeder
php artisan serve
```

API tersedia di `http://localhost:8000/api/couriers`. Seeder opsional menyediakan lima kurir (semua level), termasuk **Budiono Hadi Agung**. Seeder dapat dijalankan ulang tanpa menduplikasi kontak. Konfigurasi default memakai SQLite; jangan menjalankan `migrate:fresh` pada database yang berisi data penting.

Untuk dashboard di `http://localhost:8000`:

```sh
npm ci
npm run build
```

Selama pengembangan tampilan, gunakan `npm run dev` pada terminal terpisah. API dan pengujiannya dapat berjalan tanpa Node atau build frontend.

## Endpoint

Gunakan header `Accept: application/json` dan `Content-Type: application/json` untuk body JSON.

| Method | URL | Respons sukses |
| --- | --- | --- |
| GET | `/api/couriers` | 200; `data`, `links`, `meta` |
| POST | `/api/couriers` | 201; `data` kurir baru |
| GET | `/api/couriers/{id}` | 200; seluruh atribut dalam `data` |
| PUT/PATCH | `/api/couriers/{id}` | 200; `data` terbaru |
| DELETE | `/api/couriers/{id}` | 204; tanpa body, soft delete |
| DELETE | `/api/couriers/{id}/force` | 204; tanpa body, hapus permanen |

Tidak ditemukan: 404. Validasi gagal: 422 dengan `message` dan `errors`. Kedua metode update mendukung field parsial; field yang tidak dikirim tetap dipertahankan.

### Parameter daftar

| Parameter | Default | Aturan |
| --- | --- | --- |
| `search` | Tidak ada | String maksimal 255 karakter; setiap kata harus cocok pada nama |
| `level` | Semua level | CSV nilai 1–5, misalnya `2,3`; array query juga diterima |
| `sort` | `name` | Hanya `name` atau `created_at` |
| `direction` | `asc` | `asc` atau `desc` |
| `page` | 1 | Integer positif |
| `per_page` | 15 | Integer 1–100 |

`search=budi+agung` menemukan `Budiono Hadi Agung`. Kata boleh berbeda urutan, kapitalisasi ASCII diabaikan, dan `%`, `_`, `!` diperlakukan literal. SQLite bawaan memiliki keterbatasan case folding Unicode; kebutuhan pencarian Unicode lanjutan masuk roadmap. Filter level menggunakan OR antarlevel, kemudian AND terhadap search. Nama/tanggal yang sama diurutkan lagi berdasarkan ID agar pagination stabil. Link pagination mempertahankan query.

```sh
curl -H 'Accept: application/json' 'http://localhost:8000/api/couriers?search=budi+agung&level=2,3&sort=created_at&direction=desc&per_page=15'

curl -X POST 'http://localhost:8000/api/couriers' \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Budi Santoso","phone":"081299998888","email":"budi@example.com","level":3,"is_active":true}'

curl -X PATCH 'http://localhost:8000/api/couriers/1' \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"level":4}'

curl -X DELETE -H 'Accept: application/json' 'http://localhost:8000/api/couriers/1'

# Hapus permanen, termasuk data yang sebelumnya sudah soft delete
curl -X DELETE -H 'Accept: application/json' 'http://localhost:8000/api/couriers/1/force'
```

### Data dan validasi

- `id`: otomatis; tidak dapat diubah melalui input.
- `name`: wajib saat create, string maksimal 255 karakter, tidak boleh kosong.
- `phone`: wajib saat create, string 8–15 digit dengan opsional awalan `+`, unik. Kirim format konsisten tanpa spasi/tanda hubung; penyetaraan `08` dengan `+628` belum diterapkan.
- `email`: opsional, nullable, format email valid, maksimal 255 karakter, unik.
- `level`: wajib saat create, integer 1–5; `App\Enums\CourierLevel` menjadi enum aplikasi, divalidasi menggunakan `Rule::enum`, disimpan sebagai integer dan di-cast oleh model. JSON mengembalikan angka.
- `is_active`: boolean, default true; status ini independen dari penghapusan.
- `created_at`, `updated_at`, `deleted_at`: dikelola server, format tanggal ISO 8601 dalam respons.

Update mengabaikan record sendiri pada pemeriksaan kontak unik. Controller hanya menyimpan field yang lolos validasi. Migration tambahan memperluas tabel scaffold yang sebelumnya hanya mempunyai ID dan timestamps; tidak mengubah migration yang sudah pernah dijalankan. Kolom name, level, dan created_at memiliki index. Pencarian substring tidak dijamin memakai index nama.

### Soft delete dan force delete

Brief asli meminta data hilang dari database. Berdasarkan tambahan kebutuhan pengguna, DELETE **mengisi `deleted_at`**, bukan menghapus baris secara fisik. Data tersebut hilang dari daftar API; show, update, dan delete ulang menghasilkan 404. Test menggunakan `assertSoftDeleted`, pemeriksaan record tetap ada, dan pemeriksaan akses API sudah hilang.

Phone/email tetap dicadangkan setelah soft delete untuk mencegah konflik saat pemulihan nanti. `DELETE /api/couriers/{id}/force` menghapus baris secara permanen, baik kurir aktif maupun yang sudah soft delete. Setelah force delete, kontak dapat digunakan kembali; akses dan penghapusan ulang menghasilkan 404. Test memakai `assertDatabaseMissing` untuk membuktikan penghapusan fisik sesuai brief awal. Endpoint restore belum tersedia. API ini untuk demonstrasi tes, belum menggunakan autentikasi. Tambahkan autentikasi dan otorisasi sebelum digunakan secara operasional.

## Pengujian

```sh
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

PHPUnit menggunakan SQLite `:memory:` dan `RefreshDatabase`, terpisah dari database development. Feature tests mencakup CRUD, seluruh nilai enum, payload/query tidak valid, kontak unik, partial update, mass assignment, pagination, sorting, search multi-kata dan wildcard literal, kombinasi filter, soft delete, force delete pada data aktif/terhapus, penggunaan ulang kontak setelah force delete, serta JSON 404. Test dashboard tidak membutuhkan manifest Vite. Test browser interaktif tidak termasuk suite PHP.

## Struktur utama

- `app/Enums/CourierLevel.php`: nilai level yang sah.
- `app/Models/Courier.php`: fillable, enum/boolean cast, SoftDeletes.
- `app/Http/Requests/*CourierRequest.php`: validasi create, update, dan query.
- `app/Http/Controllers/CourierController.php`: query index dan operasi CRUD.
- `app/Http/Resources/CourierResource.php`: kontrak output.
- `database/factories` dan `database/seeders`: data testing/demo.
- `tests/Feature/CourierTest.php`: pengujian perilaku API dan persistensi.
- `resources/views/welcome.blade.php`, `resources/js/app.js`, `resources/css/app.css`: dashboard yang memanggil API, tanpa framework JS tambahan.

Controller dan Eloquent cukup untuk satu modul ini; belum ada kebutuhan service/repository layer. Dashboard mencakup search debounce, multi-level filter, sorting, pagination, tambah/edit, detail, pilihan soft delete atau force delete pada dialog konfirmasi (default soft delete), error field, loading, dan empty state. Seluruh data API ditampilkan menggunakan `textContent` untuk menghindari penyisipan HTML.

## Tahapan dan pengembangan berikutnya

1. Setup dan audit project existing.
2. Enum, migration tambahan, model, factory, seeder.
3. API CRUD dan validasi.
4. Search, filter, sort, pagination.
5. Feature test, formatter, dan dokumentasi.
6. Dashboard bonus dan build assets.
7. Pemeriksaan repository public dan penyerahan.

Roadmap setelah tes, berdasarkan kebutuhan nyata:

- Autentikasi dan policy akses sebelum pemakaian operasional.
- Tampilan arsip dan restore; otorisasi khusus penghapusan permanen.
- Audit perubahan untuk melacak pelaku dan waktu perubahan data.
- CI untuk test/formatter/build serta spesifikasi OpenAPI.
- Normalisasi telepon, Unicode search, dan evaluasi index berdasarkan volume data.
- Penugasan pengiriman/tracking sebagai modul terpisah.

Backend menjadi prioritas persyaratan penilaian; frontend adalah tambahan dan tidak mengubah kontrak API. Tidak ada jaminan tambahan nilai dari UI karena brief tidak mewajibkannya.
