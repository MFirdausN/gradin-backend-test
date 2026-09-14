const rows = document.querySelector('#courier-rows');
if (rows) {
    const $ = (selector) => document.querySelector(selector);
    const form = $('#courier-form');
    const editor = $('#editor');
    let page = 1;
    let currentId = null;
    let deleteId = null;
    let controller;
    let timer;
    const date = (value) => value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value)) : '—';
    const text = (tag, value, className = '') => {
        const node = document.createElement(tag);
        node.textContent = value;
        node.className = className;
        return node;
    };
    const notice = (message) => { $('#notice').textContent = message; $('#notice').hidden = !message; };
    async function api(path = '', options = {}) {
        const response = await fetch(`/api/couriers${path}`, { ...options, headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...options.headers } });
        const body = response.status === 204 ? null : await response.json();
        if (!response.ok) {
            const error = new Error(body.message || 'Permintaan gagal. Silakan coba lagi.');
            error.errors = body.errors;
            throw error;
        }
        return body;
    }
    function placeholder(message) {
        const row = document.createElement('tr');
        const cell = text('td', message, 'empty');
        cell.colSpan = 6;
        row.append(cell);
        rows.replaceChildren(row);
    }
    async function load() {
        controller?.abort();
        controller = new AbortController();
        const [sort, direction] = $('#sort').value.split(':');
        const params = new URLSearchParams({ page, per_page: $('#per-page').value, sort, direction });
        const search = $('#search').value.trim();
        if (search) params.set('search', search);
        const levels = [...document.querySelectorAll('[name=level]:checked')].map(input => input.value);
        if (levels.length) params.set('level', levels.join(','));
        $('#previous').disabled = $('#next').disabled = true;
        $('#total').textContent = '—';
        $('#page-summary').textContent = 'Memuat…';
        placeholder('Memuat data kurir…');
        try {
            const { data, meta } = await api(`?${params}`, { signal: controller.signal });
            if (page > meta.last_page) { page = meta.last_page; return load(); }
            rows.replaceChildren();
            for (const courier of data) {
                const row = document.createElement('tr');
                const name = document.createElement('td');
                const wrapper = text('div', '', 'courier-name');
                wrapper.append(text('span', courier.name.split(/\s+/).map(part => part[0]).slice(0, 2).join('').toUpperCase(), 'avatar'), text('span', courier.name));
                name.append(wrapper);
                const contact = text('td', courier.phone);
                contact.append(text('small', courier.email || 'Email belum ditambahkan'));
                const level = document.createElement('td');
                level.append(text('span', `Level ${courier.level}`, 'level-badge'));
                const status = document.createElement('td');
                status.append(text('span', courier.is_active ? '● Aktif' : 'Nonaktif', courier.is_active ? 'active-badge' : 'inactive-badge'));
                const actions = document.createElement('td');
                const buttons = text('div', '', 'row-actions');
                for (const [label, action] of [['Detail', () => showDetail(courier.id)], ['Edit', () => edit(courier.id)], ['Hapus', () => confirmDelete(courier)]]) {
                    const button = text('button', label);
                    button.type = 'button';
                    button.setAttribute('aria-label', `${label} ${courier.name}`);
                    button.addEventListener('click', action);
                    buttons.append(button);
                }
                actions.append(buttons);
                row.append(name, contact, level, status, text('td', date(courier.created_at)), actions);
                rows.append(row);
            }
            if (!data.length) placeholder('Belum ada kurir yang cocok. Tambahkan kurir atau ubah filter.');
            $('#total').textContent = meta.total;
            $('#page-summary').textContent = `${meta.from || 0}–${meta.to || 0} dari ${meta.total} kurir · Halaman ${meta.current_page}/${meta.last_page}`;
            $('#previous').disabled = meta.current_page <= 1;
            $('#next').disabled = meta.current_page >= meta.last_page;
        } catch (error) {
            if (error.name !== 'AbortError') {
                placeholder('Data tidak dapat dimuat. Ubah atau reset filter untuk mencoba lagi.');
                $('#page-summary').textContent = 'Gagal memuat data';
                notice(error.message);
            }
        }
    }
    function clearErrors() {
        form.querySelectorAll('[data-error]').forEach(node => { node.textContent = ''; });
        $('#form-error').textContent = '';
    }
    async function edit(id = null) {
        currentId = id;
        form.reset();
        clearErrors();
        $('#editor-title').textContent = id ? 'Edit kurir' : 'Tambah kurir';
        if (id) {
            try {
                const { data } = await api(`/${id}`);
                for (const key of ['name', 'phone', 'email', 'level']) form.elements[key].value = data[key] ?? '';
                form.elements.is_active.checked = data.is_active;
            } catch (error) { notice(error.message); return; }
        }
        editor.showModal();
    }
    async function showDetail(id) {
        try {
            const { data } = await api(`/${id}`);
            const content = $('#detail-content');
            content.replaceChildren();
            for (const [label, value] of [['ID', data.id], ['Nama', data.name], ['Telepon', data.phone], ['Email', data.email || '—'], ['Level', data.level], ['Status', data.is_active ? 'Aktif' : 'Nonaktif'], ['Terdaftar', date(data.created_at)], ['Diperbarui', date(data.updated_at)], ['Dihapus', date(data.deleted_at)]]) content.append(text('dt', label), text('dd', value));
            $('#detail').showModal();
        } catch (error) { notice(error.message); }
    }
    function confirmDelete(courier) {
        deleteId = courier.id;
        $('#delete-description').textContent = `Hapus ${courier.name} dari daftar kurir?`;
        $('#delete-error').textContent = '';
        $('#delete-dialog').showModal();
    }
    $('#confirm-delete').addEventListener('click', async () => {
        $('#confirm-delete').disabled = true;
        try {
            await api(`/${deleteId}`, { method: 'DELETE' });
            $('#delete-dialog').close();
            notice('Kurir berhasil dihapus dari daftar.');
            await load();
        } catch (error) { $('#delete-error').textContent = error.message; }
        finally { $('#confirm-delete').disabled = false; }
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();
        $('#save-courier').disabled = true;
        const body = Object.fromEntries(new FormData(form));
        body.level = Number(body.level);
        body.email = body.email || null;
        body.is_active = form.elements.is_active.checked;
        try {
            await api(currentId ? `/${currentId}` : '', { method: currentId ? 'PATCH' : 'POST', body: JSON.stringify(body) });
            editor.close();
            notice('Data kurir berhasil disimpan.');
            await load();
        } catch (error) {
            $('#form-error').textContent = 'Data belum tersimpan. Periksa isian dan coba lagi.';
            if (error.errors) {
                for (const [key, messages] of Object.entries(error.errors)) {
                    const node = form.querySelector(`[data-error="${key}"]`);
                    if (node) node.textContent = messages.join(' ');
                }
            } else $('#form-error').textContent = error.message;
        } finally { $('#save-courier').disabled = false; }
    });
    document.querySelectorAll('.close-dialog').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    $('#add-courier').addEventListener('click', () => edit());
    $('#previous').addEventListener('click', () => { page--; load(); });
    $('#next').addEventListener('click', () => { page++; load(); });
    const filterChanged = () => { page = 1; notice(''); load(); };
    $('#search').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(filterChanged, 300); });
    $('#filters').addEventListener('change', (event) => { if (event.target.id !== 'search') filterChanged(); });
    $('#filters').addEventListener('submit', (event) => { event.preventDefault(); clearTimeout(timer); filterChanged(); });
    $('#filters').addEventListener('reset', () => { clearTimeout(timer); setTimeout(filterChanged, 0); });
    load();
}
