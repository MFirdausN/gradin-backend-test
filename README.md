# Gradin Courier

Gradin Courier adalah aplikasi sederhana untuk mengelola data kurir, dibuat sebagai bagian dari programming test backend Gradin. Pengguna dapat menambahkan kurir, memperbarui informasi kontak, mencari nama, mengatur level, dan menghapus data melalui satu halaman dashboard.

Project ini menyediakan API berbasis Laravel sebagai bagian utama tes, dilengkapi tampilan web berbahasa Indonesia agar fiturnya mudah dicoba langsung melalui browser.

## Apa yang bisa dilakukan?

| Fitur | Kegunaan |
| --- | --- |
| Daftar kurir | Melihat data kurir dalam beberapa halaman agar mudah dibaca. |
| Pencarian nama | Menemukan kurir dengan satu atau beberapa kata dari namanya. |
| Filter level | Menampilkan satu atau beberapa level sekaligus, misalnya level 2 dan 3. |
| Pengurutan | Mengurutkan nama atau tanggal pendaftaran. |
| Tambah dan edit | Menyimpan data baru atau memperbarui informasi kurir. |
| Detail kurir | Melihat informasi lengkap, termasuk waktu pendaftaran dan perubahan data. |
| Arsip dan pemulihan | Menampilkan data terhapus dan mengembalikannya ke daftar. |
| Penghapusan | Memilih antara menyembunyikan data atau menghapusnya secara permanen. |

Sebagai contoh, pencarian **“budi agung”** dapat menemukan **“Budiono Hadi Agung”**. Nama tidak harus ditulis lengkap, tetapi semua kata yang dicari harus ada pada nama kurir.

## Menggunakan dashboard

Setelah aplikasi dijalankan, buka alamat yang muncul di terminal. Secara default, alamatnya adalah **http://localhost:8000**.

1. Klik **Tambah kurir** untuk memasukkan data baru. Nama, nomor telepon, dan level wajib diisi; email boleh dikosongkan.
2. Gunakan kotak pencarian, pilihan level, dan pengurutan untuk menemukan data. Klik **Reset filter** untuk kembali ke tampilan awal.
3. Klik **Detail** untuk melihat informasi lengkap atau **Edit** untuk memperbaruinya.
4. Klik **Hapus**, pilih jenis penghapusan, lalu konfirmasi pilihan tersebut.
5. Pilih **Data terhapus** pada filter status penghapusan untuk membuka arsip. Klik **Pulihkan** untuk mengembalikan kurir atau **Hapus permanen** untuk menghapusnya dari database. Pilihan **Semua data** menampilkan data yang belum dihapus beserta arsipnya.

Jika isian belum sesuai, aplikasi menampilkan pesan pada form agar pengguna tahu bagian yang perlu diperbaiki. Form tambah, edit, detail, dan hapus ditampilkan dalam jendela di tengah layar, dengan susunan yang menyesuaikan ukuran perangkat.

### Informasi yang disimpan

| Informasi | Ketentuan |
| --- | --- |
| Nama lengkap | Wajib diisi, maksimal 255 karakter. Nama boleh sama dengan kurir lain. |
| Nomor telepon | Wajib diisi dan tidak boleh dipakai kurir lain. Berisi 8–15 digit, boleh diawali `+`, tanpa spasi atau tanda hubung. |
| Email | Opsional. Jika diisi, formatnya harus valid dan tidak boleh dipakai kurir lain. |
| Level | Pilihan angka 1 sampai 5. |
| Status aktif | Menandai apakah kurir masih aktif bertugas. Kurir baru secara default berstatus aktif. |
| Tanggal pendaftaran dan perubahan | Dicatat otomatis oleh aplikasi. |

Status **nonaktif** berbeda dengan data yang dihapus. Kurir nonaktif masih tampil pada daftar, sedangkan kurir yang sudah dihapus tidak ditampilkan.

### Memilih jenis penghapusan

| Pilihan | Apa yang terjadi? | Bisa dipulihkan? |
| --- | --- | --- |
| **Soft delete** — pilihan awal | Data hilang dari daftar, tetapi tetap tersimpan di database dengan penanda waktu penghapusan. | Ya, melalui tombol **Pulihkan** pada daftar data terhapus. |
| **Force delete** | Data dihapus secara permanen dari database. | Tidak dapat dipulihkan melalui aplikasi. |

Setelah soft delete, nomor telepon dan email masih dicadangkan untuk kurir tersebut. Setelah force delete, keduanya dapat digunakan kembali pada data baru.

Dashboard menyediakan kedua pilihan untuk kurir yang belum dihapus. Pada daftar data terhapus, penghapusan hanya tersedia dalam bentuk permanen dan tetap memerlukan konfirmasi. Pemulihan mempertahankan informasi kurir, termasuk status aktif/nonaktif sebelumnya.

## Instalasi dan menjalankan aplikasi

Bagian ini ditujukan untuk orang yang menyiapkan aplikasi di komputer. Setelah prosesnya selesai, penggunaan sehari-hari cukup melalui dashboard.

### Kebutuhan

- PHP 8.3 atau lebih baru, dengan ekstensi PDO SQLite. Pengembangan diuji menggunakan PHP 8.4.
- Composer 2 untuk memasang komponen Laravel.
- Node.js 22.12 atau lebih baru dan npm untuk menyiapkan tampilan dashboard.
- Git untuk mengambil project dari repository.

Database default menggunakan **SQLite**, yang menyimpan data dalam sebuah file sehingga tidak memerlukan server database terpisah.

### 1. Ambil project dan siapkan konfigurasi

Jalankan perintah berikut di terminal:

```sh
git clone https://github.com/MFirdausN/gradin-backend-test.git
cd gradin-backend-test
composer install
cp .env.example .env
php artisan key:generate
```

File `.env` berisi pengaturan lokal aplikasi. File ini tidak disertakan dalam repository.

### 2. Siapkan database

```sh
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

Untuk mencoba aplikasi dengan lima data kurir contoh yang mencakup seluruh level, jalankan:

```sh
php artisan db:seed --class=CourierSeeder
```

Pengisian data contoh ini opsional dan dapat diulang tanpa menduplikasi kontak. Salah satu nama contohnya adalah **Budiono Hadi Agung**.

### 3. Siapkan tampilan dan jalankan aplikasi

```sh
npm ci
npm run build
php artisan serve
```

Buka **http://localhost:8000** untuk melihat dashboard. Biarkan terminal tetap berjalan selama aplikasi digunakan. Untuk menghentikannya, tekan `Ctrl+C`.

Jika hanya ingin mencoba API, langkah `npm ci` dan `npm run build` dapat dilewati. API tersedia di **http://localhost:8000/api/couriers**.

Saat mengembangkan tampilan, jalankan `npm run dev` pada terminal terpisah agar perubahan lebih mudah dilihat. Hindari menjalankan `migrate:fresh` pada database yang berisi data penting karena perintah tersebut menghapus tabel sebelum membuatnya kembali.

## Dokumentasi API

Bagian ini ditujukan untuk pengembang yang ingin menghubungkan aplikasi lain dengan data kurir. API menerima dan mengembalikan data dalam format JSON.

Gunakan header `Accept: application/json`. Untuk mengirim data JSON, tambahkan `Content-Type: application/json`.

### Daftar endpoint

Ganti `{id}` dengan ID kurir yang ingin diakses.

| Method | Endpoint | Fungsi | Status berhasil |
| --- | --- | --- | --- |
| GET | `/api/couriers` | Mengambil daftar kurir | 200 |
| POST | `/api/couriers` | Menambahkan kurir | 201 |
| GET | `/api/couriers/{id}` | Mengambil detail kurir | 200 |
| PUT / PATCH | `/api/couriers/{id}` | Memperbarui data kurir | 200 |
| DELETE | `/api/couriers/{id}` | Melakukan soft delete | 204 |
| PATCH | `/api/couriers/{id}/restore` | Memulihkan kurir yang sudah soft delete | 200 |
| DELETE | `/api/couriers/{id}/force` | Menghapus permanen, termasuk data yang sudah soft delete | 204 |

Respons daftar berisi `data`, `links`, dan `meta` untuk mendukung perpindahan halaman. Respons tambah, detail, dan edit membungkus informasi kurir dalam `data`. Penghapusan yang berhasil tidak mengembalikan body.

Kedua metode update mendukung perubahan sebagian field: informasi yang tidak dikirim tetap dipertahankan. Data yang tidak ditemukan menghasilkan **404**, sedangkan input yang tidak valid menghasilkan **422** dengan penjelasan pada `message` dan `errors`.

### Pencarian, filter, dan pengurutan

| Parameter | Nilai awal | Nilai yang diterima |
| --- | --- | --- |
| `trashed` | `without` | `without`: belum dihapus; `only`: hanya terhapus; `with`: semua data |
| `search` | Tidak ada pencarian | Teks maksimal 255 karakter |
| `level` | Semua level | Angka 1–5, dipisahkan koma, misalnya `2,3`; array query juga diterima |
| `sort` | `name` | `name` atau `created_at` |
| `direction` | `asc` | `asc` untuk naik, `desc` untuk turun |
| `page` | `1` | Bilangan bulat mulai dari 1 |
| `per_page` | `15` | Bilangan bulat antara 1 dan 100 |

Pencarian mencocokkan semua kata tanpa mewajibkan urutan yang sama. Filter `level=2,3` berarti level 2 **atau** 3. Jika pencarian dan filter digunakan bersama, hasil harus memenuhi keduanya.

Jika nilai pengurutan sama, ID digunakan sebagai urutan berikutnya agar hasil antarhalaman konsisten. Link halaman juga mempertahankan parameter yang sedang digunakan.

### Contoh penggunaan

**Mencari nama dan mengurutkan berdasarkan pendaftaran terbaru:**

```sh
curl -H 'Accept: application/json' \
  'http://localhost:8000/api/couriers?search=budi+agung&sort=created_at&direction=desc'
```

**Menampilkan kurir level 2 atau 3:**

```sh
curl -H 'Accept: application/json' \
  'http://localhost:8000/api/couriers?level=2,3'
```

**Menambahkan kurir:**

```sh
curl -X POST 'http://localhost:8000/api/couriers' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Budi Santoso","phone":"081299998888","email":"budi@example.com","level":3,"is_active":true}'
```

**Mengubah level:**

```sh
curl -X PATCH 'http://localhost:8000/api/couriers/1' \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"level":4}'
```

**Melakukan soft delete:**

```sh
curl -X DELETE -H 'Accept: application/json' \
  'http://localhost:8000/api/couriers/1'
```

**Menghapus permanen:**

```sh
curl -X DELETE -H 'Accept: application/json' \
  'http://localhost:8000/api/couriers/1/force'
```

**Menampilkan data terhapus:**

```sh
curl -H 'Accept: application/json' \
  'http://localhost:8000/api/couriers?trashed=only'
```

**Memulihkan kurir:**

```sh
curl -X PATCH -H 'Accept: application/json' \
  'http://localhost:8000/api/couriers/1/restore'
```

Pemulihan berhasil menghasilkan status **200** dan data kurir dengan `deleted_at: null`. Memulihkan kurir yang belum dihapus menghasilkan **409**; ID yang tidak ada atau sudah dihapus permanen menghasilkan **404**. Filter arsip dapat digabungkan dengan pencarian, level, pengurutan, dan pagination.

### Catatan implementasi

- Field yang dapat dikirim adalah `name`, `phone`, `email`, `level`, dan `is_active`. `email` boleh bernilai `null`; `is_active` menggunakan boolean.
- Level menggunakan PHP enum `CourierLevel`, divalidasi dengan `Rule::enum`, disimpan sebagai integer, dan dikembalikan sebagai angka pada JSON.
- `id`, `created_at`, `updated_at`, dan `deleted_at` dikelola aplikasi. Tanggal pada respons menggunakan format ISO 8601.
- Kontak unik diperiksa tanpa menganggap data kurir yang sedang diedit sebagai duplikat. Hanya field yang lolos validasi yang disimpan.
- Setelah soft delete, detail, update, dan soft delete ulang menghasilkan 404. Setelah force delete, baris benar-benar hilang dari database.
- Pencarian mengabaikan perbedaan huruf besar/kecil untuk karakter ASCII. Karakter `%`, `_`, dan `!` dicari sebagai teks biasa. Pencocokan huruf Unicode mengikuti keterbatasan SQLite bawaan.
- Nomor `08…` dan `+628…` belum disetarakan otomatis. Gunakan format kontak secara konsisten.

## Pengujian

Pengujian otomatis memeriksa apakah fitur bekerja sesuai aturan, termasuk memastikan perubahan benar-benar tersimpan dan penghapusan berjalan sesuai jenis yang dipilih.

Jalankan seluruh pengujian:

```sh
php artisan test --compact
```

Cakupannya meliputi penambahan, detail, perubahan, validasi isian, pencarian, filter, pengurutan, pembagian halaman, kedua jenis penghapusan, filter arsip, serta pemulihan data. Pengujian juga memastikan kontak dapat dipakai kembali setelah force delete dan data kurir lain tidak ikut terhapus.

Pengujian PHP memakai database SQLite sementara di memori, terpisah dari database lokal aplikasi. Pemeriksaan tampilan interaktif di browser tidak termasuk dalam suite PHP.

Untuk merapikan kode PHP yang berubah dan memastikan tampilan dapat dibangun:

```sh
vendor/bin/pint --dirty --format agent
npm run build
```

## Panduan folder untuk pengembang

| Lokasi | Isi |
| --- | --- |
| `app/Enums/CourierLevel.php` | Pilihan level kurir |
| `app/Models/Courier.php` | Pengaturan model dan soft delete |
| `app/Http/Requests/` | Aturan validasi input |
| `app/Http/Controllers/CourierController.php` | Proses pengelolaan data kurir |
| `app/Http/Resources/CourierResource.php` | Susunan respons API |
| `database/migrations/` | Struktur tabel database |
| `database/factories/` dan `database/seeders/` | Data untuk pengujian dan contoh penggunaan |
| `tests/Feature/CourierTest.php` | Pengujian API kurir |
| `resources/views/welcome.blade.php` | Struktur halaman dashboard dan form |
| `resources/css/app.css` dan `resources/js/app.js` | Tampilan serta interaksi dashboard |

## Pengembangan berikutnya

Versi ini berfokus pada satu modul pengelolaan kurir untuk kebutuhan tes. Aplikasi belum menyediakan login atau pembatasan hak akses; keduanya perlu ditambahkan sebelum digunakan secara operasional.

Pengembangan berikutnya dapat dilakukan bertahap:

1. **Login dan hak akses:** menentukan siapa yang boleh melihat, mengubah, dan menghapus data secara permanen.
2. **Pengelolaan arsip lanjutan:** menambahkan aturan masa penyimpanan arsip sesuai kebutuhan operasional.
3. **Riwayat perubahan:** mencatat siapa yang melakukan perubahan dan kapan perubahan terjadi.
4. **Peningkatan kualitas data:** menyamakan format nomor telepon dan memperluas dukungan pencarian nama.
5. **Pemeliharaan dan integrasi:** menjalankan pengujian otomatis saat kode diperbarui dan menyediakan spesifikasi API dengan OpenAPI.
6. **Modul pengiriman:** menambahkan penugasan dan pelacakan pengiriman jika dibutuhkan.
