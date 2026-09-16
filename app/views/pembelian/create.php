<?php ob_start(); ?>

<div class="app-card p-5 sm:p-6 app-reveal">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <i class="fas fa-plus-circle"></i>
                </span>
                Input Barang Masuk
            </h2>
            <p class="text-sm text-slate-500 mt-2">Pilih stok barang, atur jumlah, harga beli, harga jual, lalu simpan barang masuk.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-700 font-semibold"><i class="fas fa-list-check mr-1"></i>1. Pilih Barang</span>
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold"><i class="fas fa-sliders mr-1"></i>2. Atur Detail</span>
            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold"><i class="fas fa-floppy-disk mr-1"></i>3. Simpan</span>
        </div>
    </div>

    <form action="/pembelian/store" method="POST" id="formPembelian" onsubmit="return validateForm()">
        <div class="mb-6">
            <label for="tanggal_masuk" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Barang Masuk *</label>
            <input type="date" id="tanggal_masuk" name="tanggal" required value="<?= htmlspecialchars($tanggal_default ?? date('Y-m-d')) ?>" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">
            <p class="text-xs text-slate-500 mt-1">Pilih tanggal barang benar-benar masuk, termasuk hari sebelumnya. Stok saat ini tetap memperhitungkan penjualan setelah tanggal tersebut.</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
            <div class="lg:col-span-2 rounded-2xl border border-teal-100 bg-gradient-to-b from-teal-50/60 to-white p-4 min-w-0">
                <div class="mb-4">
                    <div class="space-y-3 mb-4">
                        <h3 class="text-base sm:text-lg font-bold leading-snug text-slate-800">Daftar Stok Barang Tersedia</h3>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span id="barang_count_info" class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-700" aria-live="polite">0 barang</span>
                            <button type="button" class="app-btn-primary inline-flex shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-3 py-2.5 text-xs sm:text-sm font-semibold" onclick="openAddBarangModal()">
                                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                                <span>Stok Barang Baru</span>
                            </button>
                        </div>
                    </div>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="search_barang_main" placeholder="Cari nama atau kode barang..." autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                    </div>
                </div>
                <div id="barang_list" class="grid grid-cols-1 gap-3 max-h-[28rem] overflow-y-auto pr-1"></div>
            </div>

            <div class="lg:col-span-3 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 h-fit min-w-0">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-slate-700">Stok Barang Dipilih</h3>
                    <span id="selected_count" class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-full font-semibold">0 item</span>
                </div>
                <div id="selected_container" class="space-y-2 max-h-[30rem] overflow-y-auto pr-1"></div>
                <p id="no_items_msg" class="text-slate-500 text-center py-5 text-sm border border-dashed border-slate-300 rounded-xl bg-white/70">
                    Belum ada barang dipilih
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-center">
                <p class="text-xs uppercase tracking-wide font-semibold text-cyan-700">Total Unit</p>
                <p class="text-2xl font-extrabold text-cyan-800" id="total_items">0</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center">
                <p class="text-xs uppercase tracking-wide font-semibold text-blue-700">Subtotal</p>
                <p class="text-2xl font-extrabold text-blue-800" id="subtotal_display">Rp 0</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                <p class="text-xs uppercase tracking-wide font-semibold text-emerald-700">Total Harga</p>
                <p class="text-2xl font-extrabold text-emerald-800" id="total_display">Rp 0</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="nama_pembeli" class="block text-slate-700 font-semibold mb-2">Nama Supplier</label>
                <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Contoh: CV Sumber Makmur"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="keterangan" class="block text-slate-700 font-semibold mb-2">Catatan (Opsional)</label>
                <input type="text" id="keterangan" name="keterangan" placeholder="Catatan transaksi pembelian"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="app-btn-primary px-6 py-3 font-semibold inline-flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>Simpan Barang Masuk
            </button>
            <a href="/pembelian" class="app-btn-secondary px-6 py-3 font-semibold inline-flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left"></i>Kembali
            </a>
        </div>
    </form>
</div>

<div id="toast" class="hidden fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg"></div>

<!-- Modal Tambah Stok Barang Baru -->
<div id="modal_add_barang" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto p-4 sm:p-6 relative">
        <button class="absolute top-3 right-3 text-slate-500 hover:text-slate-700" onclick="closeAddBarangModal()">
            <i class="fas fa-times"></i>
        </button>
        <h3 class="text-xl font-bold text-slate-800 mb-4">Tambah Stok Barang Baru</h3>
        <form id="form_add_barang" onsubmit="submitAddBarang(event)" data-skip-auto-submit-enhance="true">
            <?php include __DIR__ . '/../barang/_create-fields.php'; ?>
            <div class="flex gap-3">
                <button type="submit" class="app-btn-primary px-5 py-2">Simpan Barang</button>
                <button type="button" class="app-btn-secondary px-4 py-2" onclick="closeAddBarangModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/stock-units.js"></script>
<script src="/assets/js/money.js"></script>
<script>
let itemIndex = 0;
const allBarang = <?= json_encode(array_values($barang ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatRupiah(value) {
    const number = Number(value) || 0;
    return 'Rp ' + Math.floor(number).toLocaleString('id-ID');
}

function toDigitOnly(value) {
    return String(value ?? '').replace(/[^\d]/g, '');
}

function normalizeMoneyValue(value) {
    return MoneyID.parse(value);
}

function parseCurrencyValue(value) {
    return normalizeMoneyValue(value);
}

function formatThousandID(value) {
    return MoneyID.format(value);
}

function formatLiveMoneyInput(value) {
    return MoneyID.format(value);
}

function markPricePairInvalid(hargaBeliInput, hargaJualInput, invalid) {
    [hargaBeliInput, hargaJualInput].forEach((input) => {
        if (!input) return;
        input.classList.toggle('border-red-400', invalid);
        input.classList.toggle('ring-2', invalid);
        input.classList.toggle('ring-red-100', invalid);
    });
}

function normalizePriceInputs(scope = document) {
    scope.querySelectorAll('input[data-price-input]').forEach((input) => {
        input.value = String(MoneyID.parse(input.value));
    });
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.className = 'fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg';
    toast.classList.add(type === 'error' ? 'bg-red-100' : 'bg-emerald-100', type === 'error' ? 'text-red-700' : 'text-emerald-700', 'border', type === 'error' ? 'border-red-200' : 'border-emerald-200');
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 2600);
}

function getBarangUnitOptions(barang) {
    const rawOptions = Array.isArray(barang && barang.satuan_detail) && barang.satuan_detail.length > 0
        ? barang.satuan_detail
        : [{ satuan: barang?.satuan || 'pcs', nilai: 1, harga_beli: barang?.harga_beli || 0, harga_jual: barang?.harga_jual || 0 }];

    return rawOptions.map((unit) => ({
        satuan: String(unit?.satuan || barang?.satuan || 'pcs').trim() || (barang?.satuan || 'pcs'),
        nilai: Number(unit?.nilai ?? unit?.nilai_satuan ?? unit?.konversi ?? 1),
        harga_jual: Number(unit?.harga_jual ?? barang?.harga_jual ?? 0),
        harga_beli: Number(unit?.harga_beli ?? barang?.harga_beli ?? 0)
    })).filter((unit) => unit.satuan && unit.satuan !== '');
}

function formatKonversiText(unit, baseLabel = 'pcs') {
    const nilai = Number(unit?.nilai ?? 1);
    const safeNilai = Number.isFinite(nilai) && nilai > 0 ? nilai : 1;
    const satuan = String(unit?.satuan || baseLabel || 'pcs').trim() || (baseLabel || 'pcs');
    return `1 ${satuan} = ${safeNilai} ${baseLabel}`;
}

function resolveBarangSelection(barang, selectedSatuan = '') {
    const options = getBarangUnitOptions(barang);
    const normalized = String(selectedSatuan || '').trim();
    const match = options.find((unit) => String(unit.satuan).toLowerCase() === normalized.toLowerCase());
    return match || options[0] || { satuan: barang?.satuan || 'pcs', harga_jual: Number(barang?.harga_jual || 0), harga_beli: Number(barang?.harga_beli || 0) };
}

function createBarangCard(item) {
    return `
        <div class="border border-slate-200 rounded-xl p-3 bg-white hover:border-teal-300 hover:shadow-md transition w-full" data-barang-card="card-${item.id_barang}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <p class="font-semibold text-slate-800 leading-tight">${escapeHtml(item.nama_barang)}</p>
                    <p class="text-xs text-slate-500 mt-1">Kategori: ${escapeHtml(item.nama_kategori || '-')}</p>
                </div>
                <span class="text-[11px] bg-teal-100 text-teal-700 px-2 py-1 rounded font-mono">${escapeHtml(item.kode_barang)}</span>
            </div>
            <p class="text-sm text-slate-600">Stok: <strong>${Number(item.stok || 0)} ${escapeHtml(StockUnits.baseLabel(item))}</strong></p>
            <button type="button" class="mt-3 w-full rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold py-2" data-picker-id="${escapeHtml(item.id_barang)}" onclick="addItemFromBarangButton(this)">
                + Pilih Barang
            </button>
        </div>`;
}

function changeIncomingUnit(select, idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (!row) return;
    const id = row.querySelector('input[name*="[id_barang]"]').value;
    const barang = allBarang.find(item => String(item.id_barang) === String(id));
    if (!barang) return;
    const unit = resolveBarangSelection(barang, select.value);
    row.querySelector('input[name*="[satuan]"]').value = unit.satuan;
    row.querySelector('input[name*="[harga_satuan]"]').value = formatThousandID(unit.harga_beli);
    row.querySelector('input[name*="[harga_jual]"]').value = formatThousandID(unit.harga_jual);
    onItemChange(idx);
}

function refreshIncomingStock() {
    const rows = Array.from(document.querySelectorAll('#selected_container [data-item-index]'));
    const addedByProduct = new Map();
    const details = rows.map(row => {
        const id = row.querySelector('input[name*="[id_barang]"]').value;
        const barang = allBarang.find(item => String(item.id_barang) === String(id));
        if (!barang) return null;
        const unit = resolveBarangSelection(barang, row.querySelector('input[name*="[satuan]"]').value);
        const quantity = Math.max(0, Number(row.querySelector('input[name*="[jumlah]"]').value) || 0);
        const added = quantity * StockUnits.factor(unit);
        addedByProduct.set(id, (addedByProduct.get(id) || 0) + added);
        return { row, id, barang, unit, added };
    }).filter(Boolean);
    for (const { row, id, barang, unit, added } of details) {
        const base = StockUnits.baseLabel(barang);
        row.querySelector('[data-incoming-conversion]').textContent = formatKonversiText(unit, base);
        row.querySelector('[data-stock-before]').textContent = Number(barang.stok || 0) + ' ' + base;
        row.querySelector('[data-stock-added]').textContent = '+ ' + added + ' ' + base;
        row.querySelector('[data-stock-after]').textContent = (Number(barang.stok || 0) + addedByProduct.get(id)) + ' ' + base;
    }
}

function renderBarangList(filterText = '') {
    const listDiv = document.getElementById('barang_list');
    const info = document.getElementById('barang_count_info');
    if (!listDiv) return;

    const q = filterText.toLowerCase().trim();
    const filtered = allBarang.filter((b) => {
        const nama = String(b.nama_barang ?? '').toLowerCase();
        const kode = String(b.kode_barang ?? '').toLowerCase();
        return q === '' || nama.includes(q) || kode.includes(q);
    });

    if (info) info.textContent = `${filtered.length} barang`;

    if (filtered.length === 0) {
        listDiv.innerHTML = `
            <div class="col-span-full text-center border border-dashed border-slate-300 rounded-xl p-6 bg-white">
                <p class="text-slate-500 mb-3">Stok barang tidak ditemukan</p>
                <button type="button" class="app-btn-primary px-4 py-2 text-sm" onclick="openAddBarangModal()">
                    <i class="fas fa-plus mr-1"></i>Tambah Stok Barang Baru
                </button>
            </div>
        `;
        return;
    }

    listDiv.innerHTML = filtered.map(createBarangCard).join('');
}

function updateItemSubtotal(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (!row) return;
    const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
    const harga = parseCurrencyValue(row.querySelector('input[name*="[harga_satuan]"]').value);
    const subtotal = jumlah * harga;
    const subtotalEl = row.querySelector('.subtotal-item');
    if (subtotalEl) subtotalEl.textContent = formatRupiah(subtotal < 0 ? 0 : subtotal);
}

function addItemFromBarangButton(button) {
    if (!button) return;
    const barangId = button.getAttribute('data-picker-id');
    const barang = allBarang.find((item) => String(item.id_barang) === barangId);
    if (!barang) return;
    const card = button.closest('[data-barang-card]');
    const select = card ? card.querySelector('select[data-unit-select]') : null;
    const selectedSatuan = select ? select.value : (button.getAttribute('data-fallback-unit') || '');
    addItemFromBarang(barang, selectedSatuan);
}

function addItemFromBarang(barang, selectedSatuan = '') {
    const container = document.getElementById('selected_container');
    const noItemsMsg = document.getElementById('no_items_msg');
    const chosenUnit = resolveBarangSelection(barang, selectedSatuan);
    const finalHargaBeli = Number(chosenUnit.harga_beli ?? barang.harga_beli ?? 0);
    const finalHargaJual = Number(chosenUnit.harga_jual ?? barang.harga_jual ?? 0);
    const finalSatuan = chosenUnit.satuan || barang.satuan || 'pcs';

    const existing = Array.from(container.querySelectorAll('[data-item-index]')).find((row) => {
        const idInput = row.querySelector('input[name*="[id_barang]"]');
        const satuanInput = row.querySelector('input[name*="[satuan]"]');
        return idInput
            && satuanInput
            && parseInt(idInput.value, 10) === parseInt(barang.id_barang, 10)
            && String(satuanInput.value).trim().toLowerCase() === String(finalSatuan).trim().toLowerCase();
    });

    if (existing) {
        const qtyInput = existing.querySelector('input[name*="[jumlah]"]');
        qtyInput.value = (parseInt(qtyInput.value, 10) || 0) + 1;
        updateItemSubtotal(existing.getAttribute('data-item-index'));
        container.prepend(existing);
        hitungTotal();
        showToast('Jumlah barang ditambah');
        return;
    }

    const idx = itemIndex;
    const itemHtml = `
        <div class="border border-blue-200 bg-white rounded-xl p-3" data-barang-id="${barang.id_barang}" data-item-index="${idx}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <p class="font-semibold text-slate-800 text-sm">${escapeHtml(barang.nama_barang)}</p>
                    <p class="text-[11px] text-slate-500">${escapeHtml(barang.kode_barang)} • ${escapeHtml(barang.nama_kategori || '-')}</p>
                </div>
                <button type="button" onclick="removeItem(${idx})" class="text-xs text-red-600 hover:text-red-700 font-semibold">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
            </div>

            <div data-incoming-conversion class="mb-2 rounded-lg border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-[10px] text-sky-700 font-semibold">
                ${escapeHtml(formatKonversiText(chosenUnit, StockUnits.baseLabel(barang)))}
            </div>

            <div class="grid grid-cols-3 gap-2 mb-3 text-xs">
                <div class="rounded-lg bg-slate-50 border border-slate-200 p-2"><p class="text-slate-500">Stok sebelumnya</p><p data-stock-before class="font-bold text-slate-800">${Number(barang.stok || 0)} ${escapeHtml(StockUnits.baseLabel(barang))}</p></div>
                <div class="rounded-lg bg-sky-50 border border-sky-200 p-2"><p class="text-sky-700">Jumlah Penambahan</p><p data-stock-added class="font-bold text-sky-800">+ ${StockUnits.factor(chosenUnit)} ${escapeHtml(StockUnits.baseLabel(barang))}</p></div>
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-2"><p class="text-emerald-700">Perkiraan stok akhir</p><p data-stock-after class="font-bold text-emerald-800">${Number(barang.stok || 0) + StockUnits.factor(chosenUnit)} ${escapeHtml(StockUnits.baseLabel(barang))}</p></div>
            </div>
            <div class="grid grid-cols-2 gap-2 mb-2 text-xs">
                <div>
                    <label class="block text-slate-500 mb-1">Jumlah ditambahkan</label>
                    <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                        <button type="button" class="px-2 py-1.5 bg-slate-100" onclick="adjustQty(${idx}, -1)">-</button>
                        <input type="number" name="items[${idx}][jumlah]" value="1" min="1" class="w-full text-center py-1.5 outline-none" oninput="onItemChange(${idx})">
                        <button type="button" class="px-2 py-1.5 bg-slate-100" onclick="adjustQty(${idx}, 1)">+</button>
                    </div>
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Satuan</label>
                    <select aria-label="Satuan barang masuk" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg bg-white" onchange="changeIncomingUnit(this, ${idx})">
                        ${getBarangUnitOptions(barang).map(unit => `<option value="${escapeHtml(unit.satuan)}" ${unit.satuan === finalSatuan ? 'selected' : ''}>${escapeHtml(formatKonversiText(unit, StockUnits.baseLabel(barang)))}</option>`).join('')}
                    </select>
                    <input type="hidden" name="items[${idx}][satuan]" value="${escapeHtml(finalSatuan)}">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Harga Beli</label>
                    <input type="text" name="items[${idx}][harga_satuan]" value="${formatThousandID(finalHargaBeli)}" inputmode="numeric" autocomplete="off" data-price-input min="0" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="onItemChange(${idx})">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Harga Jual</label>
                    <input type="text" name="items[${idx}][harga_jual]" value="${formatThousandID(finalHargaJual)}" inputmode="numeric" autocomplete="off" data-price-input min="0" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="onItemChange(${idx})">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Tanggal Expired (Opsional)</label>
                    <input type="date" name="items[${idx}][tanggal_expired]" value="${barang.tanggal_expired ? String(barang.tanggal_expired).substring(0, 10) : ''}" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="onItemChange(${idx})">
                </div>
            </div>

            <div class="text-right text-xs text-slate-500">
                Subtotal: <span class="subtotal-item font-bold text-emerald-700">${formatRupiah(finalHargaBeli)}</span>
            </div>

            <input type="hidden" name="items[${idx}][id_barang]" value="${barang.id_barang}">
        </div>
    `;

    container.insertAdjacentHTML('afterbegin', itemHtml);
    noItemsMsg.style.display = 'none';
    itemIndex++;
    hitungTotal();
    showToast('Stok barang ditambahkan');
}

function adjustQty(idx, delta) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (!row) return;
    const input = row.querySelector('input[name*="[jumlah]"]');
    const next = Math.max(1, (parseInt(input.value, 10) || 1) + delta);
    input.value = next;
    onItemChange(idx);
}

function onItemChange(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (row) {
        const hargaBeliInput = row.querySelector('input[name*="[harga_satuan]"]');
        const hargaJualInput = row.querySelector('input[name*="[harga_jual]"]');
        const hargaBeli = parseCurrencyValue(hargaBeliInput?.value);
        const hargaJual = parseCurrencyValue(hargaJualInput?.value);
        const invalidPair = hargaBeli > 0 && hargaJual > 0 && hargaBeli >= hargaJual;
        markPricePairInvalid(hargaBeliInput, hargaJualInput, invalidPair);
    }
    updateItemSubtotal(idx);
    hitungTotal();
}

function removeItem(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (row) row.remove();

    const container = document.getElementById('selected_container');
    if (container.children.length === 0) {
        document.getElementById('no_items_msg').style.display = 'block';
    }
    hitungTotal();
}

function hitungTotal() {
    refreshIncomingStock();
    let totalItems = 0;
    let totalHarga = 0;
    const rows = document.querySelectorAll('#selected_container [data-item-index]');

    rows.forEach((row) => {
        updateItemSubtotal(row.getAttribute('data-item-index'));
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseCurrencyValue(row.querySelector('input[name*="[harga_satuan]"]').value);
        const hargaJual = parseCurrencyValue(row.querySelector('input[name*="[harga_jual]"]').value);
        totalItems += jumlah;
        totalHarga += (jumlah * harga);
        if (hargaJual < 0) row.querySelector('input[name*="[harga_jual]"]').value = '0';
    });

    document.getElementById('total_items').textContent = totalItems.toLocaleString('id-ID');
    document.getElementById('subtotal_display').textContent = formatRupiah(totalHarga);
    document.getElementById('total_display').textContent = formatRupiah(totalHarga);
    document.getElementById('selected_count').textContent = rows.length + ' item';
}

function validateForm() {
    const items = document.querySelectorAll('#selected_container [data-item-index]');
    if (items.length === 0) {
        showToast('Tambahkan minimal satu barang', 'error');
        return false;
    }

    let isValid = true;
    let hasEmptyHargaBeli = false;
    let hasInvalidMargin = false;
    items.forEach((row) => {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const hargaInput = row.querySelector('input[name*="[harga_satuan]"]');
        const hargaJualInput = row.querySelector('input[name*="[harga_jual]"]');
        const hargaRaw = String(hargaInput?.value || '').trim();
        const harga = parseCurrencyValue(hargaRaw);
        const hargaJual = parseCurrencyValue(hargaJualInput?.value);
        if (hargaRaw === '' || toDigitOnly(hargaRaw) === '') {
            hasEmptyHargaBeli = true;
            isValid = false;
            if (hargaInput) {
                hargaInput.classList.add('border-red-400', 'ring-2', 'ring-red-100');
            }
        } else if (hargaInput) {
            hargaInput.classList.remove('border-red-400', 'ring-2', 'ring-red-100');
        }
        if (harga > 0 && hargaJual > 0 && harga >= hargaJual) {
            hasInvalidMargin = true;
            isValid = false;
            markPricePairInvalid(hargaInput, hargaJualInput, true);
        } else {
            markPricePairInvalid(hargaInput, hargaJualInput, false);
        }
        if (jumlah < 1 || harga < 0 || hargaJual < 0) isValid = false;
    });

    if (!isValid) {
        if (hasEmptyHargaBeli) {
            showToast('Harga beli harus terisi', 'error');
        } else if (hasInvalidMargin) {
            showToast('Harga beli harus lebih kecil dari harga jual', 'error');
        } else {
            showToast('Periksa jumlah, harga beli, dan harga jual setiap item', 'error');
        }
        return false;
    }

    normalizePriceInputs(document.getElementById('formPembelian'));
    return true;
}

function openAddBarangModal() {
    document.getElementById('modal_add_barang').classList.remove('hidden');
}

function closeAddBarangModal() {
    document.getElementById('modal_add_barang').classList.add('hidden');
}

async function submitAddBarang(event) {
    event.preventDefault();
    const form = document.getElementById('form_add_barang');
    if (!window.barangFormEditors.form_add_barang.validate() || !form.reportValidity()) {
        showToast('Lengkapi satuan, isi kemasan, dan harga barang.', 'error');
        return;
    }
    const formData = new FormData(form);

    try {
        const resp = await fetch('/api/barang/store', { method: 'POST', body: formData });
        const data = await resp.json();

        if (!data.success) {
            showToast(data.message || 'Gagal menambahkan barang baru', 'error');
            return;
        }

        const barangBaru = data.barang;
        allBarang.push(barangBaru);
        window.barangFormEditors.form_add_barang.reset();
        showToast('Barang baru tersimpan. Pilih barang untuk mencatat barang masuk.');
        closeAddBarangModal();
        document.getElementById('search_barang_main').value = '';
        renderBarangList('');
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat menambahkan barang', 'error');
    }
}

document.getElementById('search_barang_main').addEventListener('input', function(e) {
    renderBarangList(e.target.value);
});

document.addEventListener('input', function (event) {
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) return;
    if (!target.matches('input[data-price-input]')) return;

    target.value = formatLiveMoneyInput(target.value);
    const caretPosition = target.value.length;
    requestAnimationFrame(() => {
        try {
            target.setSelectionRange(caretPosition, caretPosition);
        } catch (e) {
            // Ignore when the browser does not allow restoring the caret.
        }
    });
    hitungTotal();
});

renderBarangList('');
</script>

<?php
$satuan = $satuanList ?? [];
$barangFormId = 'form_add_barang';
include __DIR__ . '/../barang/_create-script.php';
$content = ob_get_clean();
$title = 'Input Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
