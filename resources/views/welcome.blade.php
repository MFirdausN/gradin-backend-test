<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gradin · Manajemen Kurir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <aside class="sidebar">
        <a class="brand" href="{{ url('/') }}"><span class="brand-mark">g.</span> gradin<span class="brand-dot">.</span></a>
        <p class="eyebrow">WORKSPACE</p>
        <a class="nav-active" href="{{ url('/') }}"><span aria-hidden="true">▦</span> Data kurir <span>↗</span></a>
        <div class="sidebar-footer"><span class="avatar">G</span><div>Courier workspace<small>Programming test</small></div></div>
    </aside>
    <main>
        <header class="topbar"><span>Workspace <span class="muted">/</span> <strong>Data kurir</strong></span><span class="workspace-badge">GRADIN BACKEND TEST</span></header>
        <section class="content">
            <div class="heading"><div><p class="eyebrow">COURIER MANAGEMENT</p><h1>Tim kurir Anda.</h1><p class="muted">Kelola informasi dan level kurir dalam satu tempat.</p></div><button id="add-courier" class="primary" type="button">＋ Tambah kurir</button></div>
            <div class="overview"><div><span class="stat-label">HASIL PENCARIAN</span><strong id="total">—</strong><span class="muted">kurir ditemukan</span></div><div><span class="stat-label">LEVEL KURIR</span><strong>1–5</strong><span class="muted">lima tingkat kurir</span></div><div class="overview-note"><span class="status-dot"></span><div><strong>Data tetap tersimpan</strong><p>Kurir yang dihapus disembunyikan dari daftar aktif.</p></div></div></div>
            <section class="panel" aria-labelledby="list-title">
                <div class="panel-title"><h2 id="list-title">Daftar kurir</h2><span class="tag">MASTER DATA</span></div>
                <form id="filters" class="filters">
                    <label class="search-label"><span class="sr-only">Cari nama kurir</span><input id="search" name="search" type="search" placeholder="Cari nama kurir…" maxlength="255"></label>
                    <label class="sort-label"><span class="sr-only">Urutkan kurir</span><select id="sort"><option value="name:asc">Nama A–Z</option><option value="name:desc">Nama Z–A</option><option value="created_at:desc">Pendaftaran terbaru</option><option value="created_at:asc">Pendaftaran terlama</option></select></label>
                    <label><span class="sr-only">Data per halaman</span><select id="per-page"><option value="15">15 / halaman</option><option value="30">30 / halaman</option><option value="100">100 / halaman</option></select></label>
                    <fieldset class="levels"><legend>Level</legend>@foreach (\App\Enums\CourierLevel::cases() as $level)<label><input type="checkbox" name="level" value="{{ $level->value }}"><span>{{ $level->value }}</span></label>@endforeach</fieldset>
                    <button class="text-button" type="reset">Reset filter</button>
                </form>
                <p id="notice" role="status" aria-live="polite" hidden></p>
                <div class="table-wrap"><table><thead><tr><th>Nama kurir</th><th>Kontak</th><th>Level</th><th>Status</th><th>Terdaftar</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody id="courier-rows"><tr><td colspan="6" class="empty">Memuat data kurir…</td></tr></tbody></table></div>
                <footer class="pagination"><span id="page-summary" class="muted">Memuat…</span><div><button id="previous" type="button" disabled>← Sebelumnya</button><button id="next" type="button" disabled>Berikutnya →</button></div></footer>
            </section>
            <footer class="page-footer">Gradin · Courier workspace <span>Laravel API + dashboard</span></footer>
        </section>
    </main>
    <dialog id="editor"><form id="courier-form"><div class="dialog-heading"><div><p class="eyebrow">DATA KURIR</p><h2 id="editor-title">Tambah kurir</h2></div><button type="button" class="close-dialog" aria-label="Tutup">✕</button></div>
        <p class="muted">Lengkapi informasi kurir. Kolom bertanda * wajib diisi.</p>
        <label>Nama lengkap *<input name="name" required maxlength="255" autocomplete="name"><small data-error="name"></small></label>
        <label>Nomor telepon *<input name="phone" required maxlength="16" placeholder="081234567890" autocomplete="tel" type="tel"><small data-error="phone"></small></label>
        <label>Email <span class="muted">(opsional)</span><input name="email" type="email" maxlength="255" autocomplete="email"><small data-error="email"></small></label>
        <label>Level *<select name="level">@foreach (\App\Enums\CourierLevel::cases() as $level)<option value="{{ $level->value }}">Level {{ $level->value }}</option>@endforeach</select><small data-error="level"></small></label>
        <label class="checkbox-label"><input name="is_active" type="checkbox" checked> Kurir aktif<small data-error="is_active"></small></label>
        <p id="form-error" role="alert"></p><div class="dialog-actions"><button type="button" class="close-dialog">Batal</button><button id="save-courier" class="primary" type="submit">Simpan kurir</button></div>
    </form></dialog>
    <dialog id="detail"><div class="dialog-heading"><h2>Detail kurir</h2><button type="button" class="close-dialog" aria-label="Tutup">✕</button></div><dl id="detail-content"></dl></dialog>
    <dialog id="delete-dialog"><h2>Hapus kurir?</h2><p id="delete-description"></p><p class="muted">Data akan disembunyikan dari daftar. Penghapusan ini bukan penghapusan permanen.</p><p id="delete-error" role="alert"></p><div class="dialog-actions"><button type="button" class="close-dialog">Batal</button><button type="button" id="confirm-delete" class="danger">Hapus kurir</button></div></dialog>
</body>
</html>
