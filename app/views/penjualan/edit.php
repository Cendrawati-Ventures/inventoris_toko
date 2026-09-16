<?php ob_start(); ?>
<?php
$normalizedRole = class_exists('PermissionGate')
    ? PermissionGate::normalizeRole((string)($_SESSION['role'] ?? 'kasir'))
    : strtolower(trim((string)($_SESSION['role'] ?? 'kasir')));
$isKasir = $normalizedRole === 'kasir';
$backUrl = $isKasir ? '/penjualan/create' : '/penjualan';
?>

<div class="app-card p-3 sm:p-4 lg:p-5 app-reveal border border-slate-200/80" style="background: linear-gradient(135deg, #f8fbfc 0%, #fffaf1 100%); box-shadow: 0 24px 64px -38px rgba(15, 23, 42, 0.5); border: 1px solid #dfe7ee;">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl text-white ring-2 ring-white" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 50%, #fbbf24 100%); box-shadow: 0 16px 28px -18px rgba(245, 158, 11, 0.9);">
                    <i class="fas fa-pen-to-square"></i>
                </span>
                Edit Penjualan
            </h2>
            <p class="text-sm sm:text-base text-slate-500 mt-2.5">Ubah barang, pembayaran, atau data hutang lalu simpan pembaruan transaksi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm">
            <span class="px-3 py-1.5 rounded-full bg-teal-100 text-teal-700 font-semibold shadow-sm"><i class="fas fa-list-check mr-1"></i>1. Pilih Barang</span>
            <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 font-semibold shadow-sm"><i class="fas fa-wallet mr-1"></i>2. Pembayaran</span>
            <span class="px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold shadow-sm"><i class="fas fa-floppy-disk mr-1"></i>3. Update</span>
        </div>
    </div>

    <?php $tanggalValue = !empty($penjualan['tanggal']) ? date('Y-m-d', strtotime($penjualan['tanggal'])) : date('Y-m-d'); ?>
    <form action="/penjualan/update/<?= $penjualan['id_penjualan'] ?>" method="POST" id="formPenjualan" onsubmit="return validateForm()">

        <div class="mb-7">
            <label for="tanggal_penjualan" class="block text-base text-slate-700 font-semibold mb-2.5">Tanggal Penjualan *</label>
            <input type="date" id="tanggal_penjualan" name="tanggal" value="<?= $tanggalValue ?>"
                   class="w-full px-4 py-3 text-base border border-slate-300 rounded-2xl bg-white shadow-sm focus:outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
            <p class="text-xs sm:text-sm text-slate-500 mt-1.5">Atur manual tanggal transaksi agar sesuai pencatatan.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[0.95fr_1.35fr] gap-4 lg:gap-5 mb-6">
            <div class="rounded-3xl border border-slate-200 p-3 sm:p-4 min-h-[260px] lg:min-h-[420px] flex flex-col" style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); box-shadow: 0 20px 34px -28px rgba(15, 23, 42, 0.72);">
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-2.5">
                        <h3 class="text-base sm:text-lg font-bold text-slate-700">Daftar Barang Tersedia</h3>
                        <span id="barang_count_info" class="text-[10px] px-2 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 font-semibold">0 barang</span>
                    </div>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="search_barang_main" placeholder="Cari nama/kode barang..." autocomplete="off"
                               class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-300 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100 mb-3 shadow-sm">
                    </div>
                </div>
                <div id="barang_list" class="grid grid-cols-1 gap-2.5 max-h-[48vh] lg:max-h-[58vh] overflow-y-auto flex-1 pr-1"></div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 min-h-[260px] lg:min-h-[420px] flex flex-col shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base sm:text-lg font-bold text-slate-700">Barang Dipilih</h3>
                    <span id="selected_count" class="text-[10px] bg-slate-800 text-white px-2.5 py-1 rounded-full font-semibold">0 item</span>
                </div>
                <div id="selected_container" class="space-y-3 max-h-[48vh] lg:max-h-[58vh] overflow-y-auto flex-1 pr-1"></div>
                <p id="no_items_msg" class="text-slate-500 text-center py-6 text-sm border border-dashed border-slate-300 rounded-xl bg-slate-50">Pilih barang dari daftar</p>
            </div>
        </div>

        <?php
            $totalItemsAwal = 0;
            $totalHargaAwal = 0;
            foreach ($details as $item) {
                $totalItemsAwal += (float)($item['jumlah'] ?? 0);
                $totalHargaAwal += ((float)($item['jumlah'] ?? 0) * (float)($item['harga_satuan'] ?? 0)) - (float)($item['diskon'] ?? 0);
            }
            $kembalianAwal = (float)($penjualan['uang_diberikan'] ?? 0) - $totalHargaAwal;
        ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
            <div class="rounded-2xl border border-cyan-200 p-4 text-center" style="background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%); box-shadow: 0 18px 28px -26px rgba(8, 145, 178, 0.6);">
                <p class="text-[11px] uppercase tracking-[0.12em] font-bold text-cyan-700">Total Item</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-cyan-800 mt-2" id="total_items"><?= number_format($totalItemsAwal, 0, ',', '.') ?></p>
            </div>
            <div class="rounded-2xl border border-blue-200 p-4 text-center" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); box-shadow: 0 18px 28px -26px rgba(59, 130, 246, 0.6);">
                <p class="text-[11px] uppercase tracking-[0.12em] font-bold text-blue-700">Subtotal</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-blue-800 mt-2" id="subtotal_display">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></p>
            </div>
            <div class="rounded-2xl border border-emerald-200 p-4 text-center" style="background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%); box-shadow: 0 18px 28px -26px rgba(16, 185, 129, 0.6);">
                <p class="text-[11px] uppercase tracking-[0.12em] font-bold text-emerald-700">Total Harga</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-emerald-800 mt-2" id="total_display">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="mb-8">
            <div id="payment_section" class="grid grid-cols-1 md:grid-cols-2 gap-6 rounded-3xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <div>
                    <label id="uang_diberikan_label" for="uang_diberikan" class="block text-base text-slate-700 font-semibold mb-2.5">Uang Diberikan Konsumen *</label>
                    <input type="text" id="uang_diberikan" name="uang_diberikan" inputmode="numeric" autocomplete="off" value="<?= number_format((float)($penjualan['uang_diberikan'] ?? 0), 0, ',', '.') ?>"
                           class="w-full px-4 py-3 text-base border border-slate-300 rounded-2xl bg-white shadow-sm focus:outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                           oninput="handleUangDiberikanInput()">
                    <p id="uang_diberikan_hint" class="text-xs sm:text-sm text-slate-500 mt-1.5">Masukkan nominal yang dibayar sekarang.</p>
                </div>
                <div>
                    <label class="block text-base text-slate-700 font-semibold mb-2.5">Kembalian</label>
                    <div class="px-4 py-3.5 bg-slate-50 border border-slate-300 rounded-2xl text-lg sm:text-xl font-bold shadow-inner" id="kembalian_display" style="color: <?= $kembalianAwal < 0 ? '#dc2626' : '#059669' ?>">
                        Rp <?= number_format($kembalianAwal, 0, ',', '.') ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="customer_section" class="mb-8" style="display:<?= $hutangData ? 'none' : 'block' ?>;">
            <label for="nama_pembeli" class="block text-base text-slate-700 font-semibold mb-2.5">Nama Pembeli</label>
            <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Masukkan nama pembeli..." value="<?= htmlspecialchars($penjualan['nama_pembeli'] ?? '') ?>"
                   class="w-full px-4 py-3 text-base border border-slate-300 rounded-2xl bg-white shadow-sm focus:outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
        </div>

        <div class="mb-6">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="ada_hutang" name="ada_hutang" onchange="toggleHutangFields()"
                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                       <?= $hutangData ? 'checked' : '' ?>>
                <label for="ada_hutang" class="ml-2 text-slate-700 font-semibold">Ada Hutang?</label>
            </div>

            <div id="hutang_section" style="display:<?= $hutangData ? 'block' : 'none' ?>;" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total Belanja</p>
                        <p id="hutang_total_display" class="text-base font-bold text-slate-800">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-600">Dibayar Sekarang</p>
                        <p id="hutang_dibayar_display" class="text-base font-bold text-emerald-700">Rp <?= number_format((float)($penjualan['uang_diberikan'] ?? 0), 0, ',', '.') ?></p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-600">Sisa Hutang</p>
                        <p id="hutang_sisa_display" class="text-base font-bold text-amber-700">Rp <?= isset($hutangData['jumlah_hutang']) ? number_format($hutangData['jumlah_hutang'], 0, ',', '.') : '0' ?></p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="nama_penghutang" class="block text-slate-700 font-semibold mb-2">Nama Yang Ngutang *</label>
                        <input type="text" id="nama_penghutang" name="nama_penghutang" placeholder="Siapa yang ngutang?"
                               value="<?= htmlspecialchars($hutangData['nama_penghutang'] ?? '') ?>"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                    </div>
                    <div>
                        <label for="jatuh_tempo" class="block text-slate-700 font-semibold mb-2">Tanggal Jatuh Tempo *</label>
                        <input type="date" id="jatuh_tempo" name="jatuh_tempo" value="<?= $hutangData['jatuh_tempo'] ?? '' ?>"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                    </div>
                </div>
                <div>
                    <label for="jumlah_hutang_display" class="block text-slate-700 font-semibold mb-2">Jumlah Hutang (Sisa)</label>
                    <div class="px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-lg font-bold text-green-600" id="jumlah_hutang_display">
                        Rp <?= isset($hutangData['jumlah_hutang']) ? number_format($hutangData['jumlah_hutang'], 0, ',', '.') : '0' ?>
                    </div>
                    <input type="hidden" id="jumlah_hutang" name="jumlah_hutang" value="<?= $hutangData['jumlah_hutang'] ?? '0' ?>">
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full pt-2">
            <button type="submit" class="w-full sm:w-auto app-btn-primary px-6 py-3.5 text-base transition flex items-center justify-center gap-2 font-semibold shadow-lg shadow-teal-500/15">
                <i class="fas fa-save"></i>
                Update Penjualan
            </button>

            <button type="button" onclick="printNota()" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-base rounded-xl transition flex items-center justify-center gap-2 font-semibold shadow-lg shadow-emerald-500/15">
                <i class="fas fa-print"></i>
                Cetak
            </button>

            <a href="<?= htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8') ?>" class="w-full sm:w-auto app-btn-secondary px-6 py-3.5 text-base transition flex items-center justify-center gap-2 font-semibold">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </form>
</div>

<div id="toast" class="hidden fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg"></div>

<div id="modal_tambah_stok" class="fixed inset-0 bg-black/40 hidden z-[70] items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl p-5 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">Tambah Stok Barang</h3>
            <button type="button" onclick="closeTambahStokModal()" class="text-slate-500 hover:text-slate-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p id="stok_modal_barang_nama" class="text-sm font-semibold text-slate-700 mb-1">-</p>
        <p id="stok_modal_barang_info" class="text-xs text-slate-500 mb-4">-</p>
        <form id="form_tambah_stok" onsubmit="submitTambahStok(event)">
            <input type="hidden" id="stok_modal_id_barang" value="">
            <div class="mb-4">
                <label for="stok_modal_satuan" class="block text-sm font-semibold text-slate-700 mb-2">Satuan yang ditambahkan</label>
                <select id="stok_modal_satuan" class="w-full px-3 py-2 border rounded-xl mb-3"></select>
                <label for="stok_modal_jumlah" class="block text-sm font-semibold text-slate-700 mb-2">Jumlah Stok Ditambahkan</label>
                <input type="number" id="stok_modal_jumlah" min="1" value="1" required
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="app-btn-primary px-4 py-2.5 font-semibold">Simpan</button>
                <button type="button" class="app-btn-secondary px-4 py-2.5 font-semibold" onclick="closeTambahStokModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/stock-units.js"></script>
<script>
let itemIndex = 0;
const allBarang = <?= json_encode(array_values($barang ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
const existingDetails = <?= json_encode($details) ?>;
const notaConfig = Object.assign({
    nama_toko: 'UD. BERSAUDARA',
    alamat_toko: '',
    nomor_telepon: '',
    email_toko: '',
    footer_nota: '',
    lebar_kertas: 80,
    tampilkan_jam: 1,
    tampilkan_kode_barang: 1,
    tampilkan_satuan: 1,
    jumlah_diskon_terpisah: 0,
    custom_header_text: '',
    custom_footer_text: '',
    tampilkan_nama_pembeli: 1,
    tampilkan_info_hutang: 1
}, <?= json_encode($notaConfig ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>);
let stockModalBarang = null;

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.className = 'fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg';
    toast.classList.add(type === 'error' ? 'bg-red-100' : 'bg-emerald-100', type === 'error' ? 'text-red-700' : 'text-emerald-700', 'border', type === 'error' ? 'border-red-200' : 'border-emerald-200');
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 2600);
}

function formatRupiah(value) {
    return 'Rp ' + Math.floor(Number(value) || 0).toLocaleString('id-ID');
}

function parseNominalInput(value) {
    const digits = String(value ?? '').replace(/[^\d]/g, '');
    return parseInt(digits || '0', 10);
}

function formatNominalInput(value) {
    return (parseInt(value, 10) || 0).toLocaleString('id-ID');
}

function handleUangDiberikanInput() {
    const input = document.getElementById('uang_diberikan');
    if (!input) return;
    const numericValue = parseNominalInput(input.value);
    input.value = formatNominalInput(numericValue);
    hitungKembalian();
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getBarangUnitOptions(barang) {
    const rawOptions = Array.isArray(barang && barang.satuan_detail) && barang.satuan_detail.length > 0
        ? barang.satuan_detail
        : [{ satuan: barang?.satuan || 'pcs', harga_beli: barang?.harga_beli || 0, harga_jual: barang?.harga_jual || 0 }];

    return rawOptions.map((unit) => ({
        satuan: String(unit?.satuan || barang?.satuan || 'pcs').trim() || (barang?.satuan || 'pcs'),
        nilai: Number(unit?.nilai ?? unit?.nilai_satuan ?? unit?.konversi ?? 1),
        harga_jual: Number(unit?.harga_jual ?? barang?.harga_jual ?? 0),
        harga_beli: Number(unit?.harga_beli ?? barang?.harga_beli ?? 0)
    })).filter((unit) => unit.satuan && unit.satuan !== '');
}

function resolveBarangSelection(barang, selectedSatuan = '') {
    const options = getBarangUnitOptions(barang);
    const normalized = String(selectedSatuan || '').trim();
    const match = options.find((unit) => String(unit.satuan).toLowerCase() === normalized.toLowerCase());
    return match || options[0] || { satuan: barang?.satuan || 'pcs', harga_jual: Number(barang?.harga_jual || 0), harga_beli: Number(barang?.harga_beli || 0) };
}

function syncBarangUnitPreview(select) {
    if (!select) return;
    const barangId = select.getAttribute('data-unit-select');
    const barang = allBarang.find((item) => String(item.id_barang) === String(barangId));
    if (!barang) return;
    const preview = document.getElementById('barang_price_preview_' + barangId);
    const selected = resolveBarangSelection(barang, select.value);
    if (preview) preview.textContent = formatRupiah(selected.harga_jual || 0);
    const info = document.getElementById('barang_unit_info_' + barangId);
    if (info) info.textContent = formatKonversiText(selected, StockUnits.baseLabel(barang)) + '; tersedia ' + Math.floor(availableBaseStock(barang) / StockUnits.factor(selected)) + ' ' + selected.satuan;
}

function formatKonversiText(unit, baseLabel = 'pcs') {
    return '1 ' + unit.satuan + ' = ' + StockUnits.factor(unit) + ' ' + baseLabel;
}

function availableBaseStock(barang) {
    return StockUnits.available(barang, typeof existingDetails === 'undefined' ? [] : existingDetails);
}

function selectedStockRows() {
    return Array.from(document.querySelectorAll('[data-item-index]')).map(row => ({
        id: row.querySelector('input[name*="[id_barang]"]').value,
        quantity: Number(row.querySelector('input[name*="[jumlah]"]').value) || 0,
        nilai: Number(row.getAttribute('data-nilai-satuan')) || 1
    }));
}

function stockExceeded(row) {
    const id = row.querySelector('input[name*="[id_barang]"]').value;
    return StockUnits.required(selectedStockRows(), id) > Number(row.getAttribute('data-stok'));
}

function refreshStockWarnings() {
    for (const row of document.querySelectorAll('[data-item-index]')) {
        const id = row.querySelector('input[name*="[id_barang]"]').value;
        const barang = allBarang.find(item => String(item.id_barang) === String(id));
        const used = StockUnits.required(selectedStockRows(), id);
        const stock = Number(row.getAttribute('data-stok'));
        const warning = document.getElementById('warning_' + row.getAttribute('data-item-index'));
        if (warning) {
            warning.textContent = 'Total dipilih: ' + used + ' ' + StockUnits.baseLabel(barang || {}) + '; stok tersedia: ' + stock;
            warning.classList.toggle('hidden', used <= stock);
        }
    }
}

function renderBarangList(filterText = '') {
    const listDiv = document.getElementById('barang_list');
    const countInfo = document.getElementById('barang_count_info');
    if (!listDiv) return;

    const q = filterText.toLowerCase().trim();
    const filtered = [];

    for (let i = 0; i < allBarang.length; i++) {
        const b = allBarang[i];
        const nama = String(b.nama_barang ?? '').toLowerCase().trim();
        const kode = String(b.kode_barang ?? '').toLowerCase().trim();
        if (q === '' || nama.includes(q) || kode.includes(q)) {
            filtered.push(b);
        }
    }

    if (countInfo) {
        countInfo.textContent = filtered.length + ' barang';
    }

    if (filtered.length === 0) {
        listDiv.innerHTML = '<div class="col-span-full text-center border border-dashed border-slate-300 rounded-xl p-6 bg-white text-slate-500">Tidak ada barang</div>';
        return;
    }

    let html = '';
    for (let i = 0; i < filtered.length; i++) {
        const item = filtered[i];
        const stock = availableBaseStock(item);
        const stockBadgeClass = stock <= 0
            ? 'bg-red-100 text-red-700'
            : stock <= 10
                ? 'bg-amber-100 text-amber-700'
                : 'bg-emerald-100 text-emerald-700';
        const unitOptions = getBarangUnitOptions(item);
        const firstUnit = unitOptions[0] || { satuan: item.satuan || 'pcs', harga_jual: Number(item.harga_jual || 0) };

        html += '<div class="h-full flex flex-col rounded-2xl border border-slate-200 p-4 transition" data-barang-card="card-' + item.id_barang + '" style="background: linear-gradient(135deg, #ffffff 0%, #fff7ed 45%, #fef3c7 100%); box-shadow: 0 18px 30px -24px rgba(15, 23, 42, 0.72); border-color: #fde68a;">';
        html += '<div class="flex items-start justify-between gap-3 mb-3">';
        html += '<div class="min-w-0"><p class="font-bold text-base text-slate-800 leading-snug min-h-[2.8rem]">' + escapeHtml(item.nama_barang) + '</p>';
        html += '<p class="text-sm text-slate-500 mt-1">' + escapeHtml(item.nama_kategori || '-') + '</p></div>';
        html += '<span class="shrink-0 text-[10px] bg-slate-100 text-slate-600 px-2 py-1 rounded-full font-mono">' + escapeHtml(item.kode_barang) + '</span></div>';
        html += '<div class="rounded-xl bg-slate-50 border border-slate-200 p-3 space-y-2.5 mb-3">';
        html += '<div class="flex items-center justify-between text-sm text-slate-600"><span>Harga Jual</span><span id="barang_price_preview_' + item.id_barang + '" class="font-bold text-emerald-700">' + formatRupiah(firstUnit.harga_jual || 0) + '</span></div>';
        if (unitOptions.length > 1) {
            html += '<div class="space-y-1.5"><label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Pilih Satuan</label>';
            html += '<select data-unit-select="' + item.id_barang + '" class="w-full rounded-xl border border-slate-300 bg-white px-2.5 py-2.5 text-sm text-slate-700 focus:outline-none focus:border-teal-500" onchange="syncBarangUnitPreview(this)">';
            for (const unit of unitOptions) {
                html += '<option value="' + escapeHtml(unit.satuan) + '" ' + (unit.satuan === firstUnit.satuan ? 'selected' : '') + '>' + escapeHtml(unit.satuan) + '</option>';
            }
            html += '</select></div>';
        }
        html += '<p id="barang_unit_info_' + item.id_barang + '" class="text-xs text-slate-600 mb-2">' + escapeHtml(formatKonversiText(firstUnit, StockUnits.baseLabel(item))) + '; tersedia ' + Math.floor(stock / StockUnits.factor(firstUnit)) + ' ' + escapeHtml(firstUnit.satuan) + '</p>';
        html += '<div class="grid grid-cols-2 gap-2.5">';
        html += '<button type="button" class="w-full rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white text-sm font-semibold py-2.5 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-sm" data-barang-id="' + escapeHtml(item.id_barang) + '" data-fallback-unit="' + escapeHtml(firstUnit.satuan || '') + '" onclick="addItemFromBarangButton(this)" ' + (stock <= 0 ? 'disabled' : '') + '>' + (stock <= 0 ? 'Stok Habis' : 'Pilih') + '</button>';
        html += '<button type="button" class="w-full rounded-xl border border-cyan-200 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-sm font-semibold py-2.5 transition" data-barang-id="' + escapeHtml(item.id_barang) + '" onclick="openTambahStokFromButton(this)">Tambah</button>';
        html += '</div>';
        html += '<div class="flex items-center justify-between text-sm"><span class="text-slate-500">Stok</span><span class="' + stockBadgeClass + ' px-2.5 py-1 rounded-full font-semibold">' + stock + ' ' + escapeHtml(StockUnits.baseLabel(item)) + '</span></div>';
        html += '</div></div>';
    }
    listDiv.innerHTML = html;
}

function addItemFromBarangButton(button) {
    if (!button) return;

    const barangId = button.getAttribute('data-barang-id');

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
    const finalHarga = Number(chosenUnit.harga_jual || barang.harga_jual || 0);
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
        checkStokAndHitung(existing.getAttribute('data-item-index'));
        container.prepend(existing);
        showToast('Jumlah item ditambah');
        return;
    }

    appendSelectedItem({
        id_barang: barang.id_barang,
        nama_barang: barang.nama_barang,
        kode_barang: barang.kode_barang,
        satuan: finalSatuan,
        harga_satuan: finalHarga,
        jumlah: 1,
        diskon: 0,
        nilai_satuan: StockUnits.factor(chosenUnit),
        stok: availableBaseStock(barang)
    }, true);

    noItemsMsg.style.display = 'none';
    showToast('Barang ditambahkan');
}

function addItemFromDetail(detail) {
    const barangMatch = allBarang.find((b) => String(b.id_barang) === String(detail.id_barang)) || {};
    appendSelectedItem({
        id_barang: detail.id_barang,
        nama_barang: detail.nama_barang || barangMatch.nama_barang || 'Item',
        kode_barang: detail.kode_barang || barangMatch.kode_barang || '-',
        satuan: detail.satuan || barangMatch.satuan || '',
        harga_satuan: detail.harga_satuan || barangMatch.harga_jual || 0,
        jumlah: detail.jumlah || 1,
        diskon: detail.diskon || 0,
        nilai_satuan: Number(detail.nilai_satuan) || 1,
        stok: availableBaseStock(barangMatch)
    }, false);

    document.getElementById('no_items_msg').style.display = 'none';
}

function appendSelectedItem(item, toTop = false) {
    const container = document.getElementById('selected_container');
    const harga = parseFloat(item.harga_satuan) || 0;
    const jumlah = parseFloat(item.jumlah) || 1;
    const diskon = parseFloat(item.diskon) || 0;
    const subtotal = (jumlah * harga) - diskon;

    let itemHtml = '<div class="border rounded-2xl p-3 transition" data-item-index="' + itemIndex + '" data-nilai-satuan="' + (Number(item.nilai_satuan) || 1) + '" data-stok="' + (Number(item.stok) || 0) + '" style="background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%); border-color: #fcd34d; box-shadow: 0 18px 30px -24px rgba(245, 158, 11, 0.75);">';
    itemHtml += '<div class="flex items-start justify-between gap-3 mb-3"><div>';
    itemHtml += '<p class="font-bold text-slate-800 text-sm leading-tight">' + escapeHtml(item.nama_barang) + '</p>';
    itemHtml += '<p class="text-[10px] text-slate-500 mt-1">' + escapeHtml(item.kode_barang) + ' • ' + escapeHtml(item.satuan) + '</p></div>';
    itemHtml += '<button type="button" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-[10px] text-red-600 hover:bg-red-100 font-semibold" onclick="removeItem(' + itemIndex + ')"><i class="fas fa-trash"></i>Hapus</button></div>';
    itemHtml += '<div id="warning_' + itemIndex + '" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg px-2.5 py-1.5 mb-2 text-[10px] text-yellow-700"><i class="fas fa-exclamation-triangle mr-1"></i>Jumlah melebihi stok tersedia (' + (parseInt(item.stok, 10) || 0) + ' ' + escapeHtml(item.satuan) + ')</div>';
    itemHtml += '<div class="grid grid-cols-2 gap-2 text-xs">';
    itemHtml += '<div><label class="block text-slate-500 mb-1 font-medium">Jumlah</label>';
    itemHtml += '<div class="flex items-center border border-slate-300 rounded-lg overflow-hidden bg-slate-50 shadow-sm"><button type="button" class="px-2.5 py-2 bg-slate-100 text-base font-bold text-slate-700" onclick="adjustQty(' + itemIndex + ', -1)">-</button>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][jumlah]" value="' + jumlah + '" min="1" class="w-full text-center py-2 outline-none bg-transparent text-sm font-semibold text-slate-700" onchange="checkStokAndHitung(' + itemIndex + ')">';
    itemHtml += '<button type="button" class="px-2.5 py-2 bg-slate-100 text-base font-bold text-slate-700" onclick="adjustQty(' + itemIndex + ', 1)">+</button></div></div>';
    itemHtml += '<div><label class="block text-slate-500 mb-1 font-medium">Harga</label>';
    itemHtml += '<div class="px-2.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-700 font-semibold shadow-sm">' + formatRupiah(harga) + '</div></div>';
    itemHtml += '<div><label class="block text-slate-500 mb-1 font-medium">Satuan</label>';
    itemHtml += '<div class="px-2.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-700 shadow-sm">' + escapeHtml(item.satuan) + '</div></div>';
    itemHtml += '<div><label class="block text-slate-500 mb-1 font-medium">Diskon</label>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][diskon]" value="' + diskon + '" min="0" class="w-full px-2.5 py-2.5 border border-slate-300 rounded-lg bg-white shadow-sm" onchange="hitungTotal()"></div></div>';
    itemHtml += '<div class="mt-2.5 rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1.5 text-right text-xs text-emerald-700">Subtotal: <span id="subtotal_' + itemIndex + '" class="font-bold">' + formatRupiah(subtotal) + '</span></div>';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][id_barang]" value="' + item.id_barang + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][nama_barang]" value="' + escapeHtml(item.nama_barang) + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][kode_barang]" value="' + escapeHtml(item.kode_barang) + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][satuan]" value="' + escapeHtml(item.satuan) + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][harga_satuan]" value="' + harga + '"></div>';

    container.insertAdjacentHTML(toTop ? 'afterbegin' : 'beforeend', itemHtml);
    itemIndex++;
    updateSelectedCount();
    hitungTotal();
}

function adjustQty(idx, delta) {
    const row = document.querySelector('[data-item-index="' + idx + '"]');
    if (!row) return;
    const input = row.querySelector('input[name*="[jumlah]"]');
    const next = Math.max(1, (parseInt(input.value, 10) || 1) + delta);
    input.value = next;
    checkStokAndHitung(idx);
}

function checkStokAndHitung(idx) {
    const row = document.querySelector('[data-item-index="' + idx + '"]');
    if (!row) return;

    const stok = parseInt(row.getAttribute('data-stok')) || 0;
    const jumlahInput = row.querySelector('input[name*="[jumlah]"]');
    const jumlah = parseInt(jumlahInput.value, 10) || 0;
    const warningDiv = document.getElementById('warning_' + idx);

    if (stockExceeded(row)) {
        warningDiv.classList.remove('hidden');
        jumlahInput.classList.add('ring-2', 'ring-amber-300', 'border-amber-400');
    } else {
        warningDiv.classList.add('hidden');
        jumlahInput.classList.remove('ring-2', 'ring-amber-300', 'border-amber-400');
    }

    hitungTotal();
}

function removeItem(idx) {
    const row = document.querySelector('[data-item-index="' + idx + '"]');
    if (row) row.remove();

    const container = document.getElementById('selected_container');
    if (container.children.length === 0) {
        document.getElementById('no_items_msg').style.display = 'block';
    }

    updateSelectedCount();
    hitungTotal();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('[data-item-index]').length;
    document.getElementById('selected_count').textContent = count + ' item';
}

function hitungTotal() {
    refreshStockWarnings();
    let totalItems = 0;
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-item-index]');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const idx = row.getAttribute('data-item-index');
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        const subtotal = (jumlah * harga) - diskon;
        totalItems += jumlah;
        totalHarga += subtotal;

        const subtotalEl = document.getElementById('subtotal_' + idx);
        if (subtotalEl) {
            subtotalEl.textContent = formatRupiah(subtotal);
        }
    }

    document.getElementById('total_items').textContent = totalItems.toLocaleString('id-ID');
    document.getElementById('total_display').textContent = formatRupiah(totalHarga);
    document.getElementById('subtotal_display').textContent = formatRupiah(totalHarga);
    updateHutangSummary(totalHarga);

    updateSelectedCount();
    hitungKembalian();
}

function hitungKembalian() {
    const uangDiberikan = parseNominalInput(document.getElementById('uang_diberikan').value);
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-item-index]');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        totalHarga += (jumlah * harga) - diskon;
    }

    const kembalian = uangDiberikan - totalHarga;
    const display = document.getElementById('kembalian_display');
    display.textContent = formatRupiah(kembalian);
    display.style.color = kembalian < 0 ? '#dc2626' : '#059669';
    updateHutangSummary(totalHarga);
}

function getTotalHargaCurrent() {
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-item-index]');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        totalHarga += (jumlah * harga) - diskon;
    }
    return totalHarga;
}

function updateHutangSummary(totalHarga = null) {
    const totalBelanja = totalHarga === null ? getTotalHargaCurrent() : totalHarga;
    const uangDibayar = parseNominalInput(document.getElementById('uang_diberikan').value);
    const sisaHutang = Math.max(totalBelanja - uangDibayar, 0);

    const totalEl = document.getElementById('hutang_total_display');
    const bayarEl = document.getElementById('hutang_dibayar_display');
    const sisaEl = document.getElementById('hutang_sisa_display');
    if (totalEl) totalEl.textContent = formatRupiah(totalBelanja);
    if (bayarEl) bayarEl.textContent = formatRupiah(uangDibayar);
    if (sisaEl) sisaEl.textContent = formatRupiah(sisaHutang);

    const jumlahHutangInput = document.getElementById('jumlah_hutang');
    const jumlahHutangDisplay = document.getElementById('jumlah_hutang_display');
    if (jumlahHutangInput) jumlahHutangInput.value = Math.floor(sisaHutang);
    if (jumlahHutangDisplay) {
        jumlahHutangDisplay.textContent = formatRupiah(sisaHutang);
        jumlahHutangDisplay.style.color = sisaHutang > 0 ? '#b45309' : '#059669';
    }
}

function openTambahStokFromButton(button) {
    const barang = allBarang.find((item) => String(item.id_barang) === button.getAttribute('data-barang-id'));
    if (barang) openTambahStokModal(barang);
}

function openTambahStokModal(barang) {
    stockModalBarang = barang;
    document.getElementById('stok_modal_id_barang').value = barang.id_barang;
    document.getElementById('stok_modal_barang_nama').textContent = barang.nama_barang || '-';
    document.getElementById('stok_modal_barang_info').textContent = `Stok saat ini: ${barang.stok || 0} ${StockUnits.baseLabel(barang)} (satuan dasar)`;
    document.getElementById('stok_modal_satuan').innerHTML = getBarangUnitOptions(barang).map(unit => '<option value="' + escapeHtml(unit.satuan) + '">' + escapeHtml(formatKonversiText(unit, StockUnits.baseLabel(barang))) + '</option>').join('');
    document.getElementById('stok_modal_jumlah').value = 1;
    document.getElementById('modal_tambah_stok').classList.remove('hidden');
    document.getElementById('modal_tambah_stok').classList.add('flex');
}

function closeTambahStokModal() {
    document.getElementById('modal_tambah_stok').classList.add('hidden');
    document.getElementById('modal_tambah_stok').classList.remove('flex');
    stockModalBarang = null;
}

async function submitTambahStok(event) {
    event.preventDefault();
    const idBarang = parseInt(document.getElementById('stok_modal_id_barang').value, 10) || 0;
    const jumlahTambah = parseInt(document.getElementById('stok_modal_jumlah').value, 10) || 0;

    if (idBarang <= 0 || jumlahTambah <= 0) {
        showToast('Jumlah tambah stok harus lebih dari 0', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id_barang', String(idBarang));
    formData.append('jumlah_tambah', String(jumlahTambah));
    formData.append('satuan', document.getElementById('stok_modal_satuan').value);

    try {
        const resp = await fetch('/api/barang/tambah-stok', { method: 'POST', body: formData });
        const data = await resp.json();
        if (!data.success) {
            showToast(data.message || 'Gagal menambah stok', 'error');
            return;
        }

        const updated = data.barang || null;
        if (updated) {
            for (let i = 0; i < allBarang.length; i++) {
                if (parseInt(allBarang[i].id_barang, 10) === parseInt(updated.id_barang, 10)) {
                    allBarang[i].stok = updated.stok;
                    break;
                }
            }

            document.querySelectorAll('[data-item-index]').forEach((row) => {
                const idInput = row.querySelector('input[name*="[id_barang]"]');
                if (idInput && parseInt(idInput.value, 10) === parseInt(updated.id_barang, 10)) {
                    row.setAttribute('data-stok', String(availableBaseStock(allBarang.find(item => String(item.id_barang) === String(updated.id_barang)))));
                    const idx = row.getAttribute('data-item-index');
                    const warningDiv = document.getElementById('warning_' + idx);
                    if (warningDiv) {
                        warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Jumlah melebihi stok tersedia (' + updated.stok + ' ' + (updated.satuan || '') + ')';
                    }
                    checkStokAndHitung(idx);
                }
            });
        }

        const keyword = document.getElementById('search_barang_main')?.value || '';
        renderBarangList(keyword);
        closeTambahStokModal();
        showToast('Stok berhasil ditambahkan');
    } catch (error) {
        console.error(error);
        showToast('Terjadi kesalahan saat menambah stok', 'error');
    }
}

function validateForm() {
    const tanggalPenjualan = document.getElementById('tanggal_penjualan');
    if (!tanggalPenjualan.value) {
        showToast('Tanggal penjualan wajib diisi', 'error');
        return false;
    }

    const items = document.querySelectorAll('[data-item-index]');
    if (items.length === 0) {
        showToast('Tambahkan minimal satu item', 'error');
        return false;
    }

    let barangMelebihi = [];

    for (let i = 0; i < items.length; i++) {
        const row = items[i];
        const jumlah = parseInt(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const stok = parseInt(row.getAttribute('data-stok')) || 0;
        const namaBr = row.querySelector('input[name*="[nama_barang]"]')?.value || 'Barang';

        if (!jumlah || jumlah < 1) {
            showToast('Lengkapi semua item barang dan jumlah', 'error');
            return false;
        }

        if (stockExceeded(row)) {
            barangMelebihi.push(`${namaBr} (jumlah: ${jumlah}, stok: ${stok})`);
        }
    }

    if (barangMelebihi.length > 0) {
        showToast('Ada item yang melebihi stok tersedia', 'error');
        return false;
    }

    if (document.getElementById('ada_hutang').checked) {
        const namaPenghutang = document.getElementById('nama_penghutang').value.trim();
        const jatuhTempo = document.getElementById('jatuh_tempo').value;
        if (!namaPenghutang || !jatuhTempo) {
            showToast('Lengkapi data hutang terlebih dahulu', 'error');
            return false;
        }
        const sisaHutang = parseFloat(document.getElementById('jumlah_hutang').value) || 0;
        if (sisaHutang <= 0) {
            showToast('Tidak ada sisa hutang. Nonaktifkan "Ada Hutang" jika pembayaran sudah lunas.', 'error');
            return false;
        }
    }

    const uangDiberikanInput = document.getElementById('uang_diberikan');
    if (uangDiberikanInput) {
        uangDiberikanInput.value = String(parseNominalInput(uangDiberikanInput.value));
    }

    return true;
}

function toggleHutangFields() {
    const hutangSection = document.getElementById('hutang_section');
    const customerSection = document.getElementById('customer_section');
    const adaHutang = document.getElementById('ada_hutang').checked;
    const namaPembeli = document.getElementById('nama_pembeli');
    const namaPenghutang = document.getElementById('nama_penghutang');
    const uangLabel = document.getElementById('uang_diberikan_label');
    const uangHint = document.getElementById('uang_diberikan_hint');

    hutangSection.style.display = adaHutang ? 'block' : 'none';
    customerSection.style.display = adaHutang ? 'none' : 'block';
    if (uangLabel) {
        uangLabel.textContent = adaHutang ? 'Uang Dibayar Sekarang *' : 'Uang Diberikan Konsumen *';
    }
    if (uangHint) {
        uangHint.textContent = adaHutang
            ? 'Masukkan nominal yang dibayar sekarang. Sisa otomatis jadi hutang.'
            : 'Masukkan nominal yang dibayar sekarang.';
    }

    if (adaHutang) {
        if (namaPembeli.value) {
            namaPenghutang.value = namaPembeli.value;
        }
        updateHutangSummary();
    } else {
        document.getElementById('jumlah_hutang').value = 0;
        document.getElementById('jumlah_hutang_display').textContent = 'Rp 0';
        updateHutangSummary();
    }
}

function getTanggalNota() {
    const input = document.getElementById('tanggal_penjualan');
    const now = new Date();
    let tanggalStr = ('0' + now.getDate()).slice(-2) + '/' + ('0' + (now.getMonth() + 1)).slice(-2) + '/' + now.getFullYear();
    if (input && input.value && input.value.includes('-')) {
        const [y, m, d] = input.value.split('-');
        tanggalStr = d + '/' + m + '/' + y;
    }
    const waktuStr = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
    return { tanggal: tanggalStr, waktu: waktuStr };
}

function printNota() {
    const items = document.querySelectorAll('[data-item-index]');
    if (items.length === 0) {
        showToast('Tambahkan minimal satu item untuk print nota', 'error');
        return;
    }
    const cfg = notaConfig || {};
    const width = parseInt(cfg.lebar_kertas || 80, 10);
    const fontNota = cfg.font_nota || 'Arial';
    const marginTop = Math.max(0, parseFloat(cfg.margin_nota_atas ?? 1.5) || 1.5);
    const marginRight = Math.max(0, parseFloat(cfg.margin_nota_kanan ?? 1.5) || 1.5);
    const marginBottom = Math.max(0, parseFloat(cfg.margin_nota_bawah ?? 1.5) || 1.5);
    const marginLeft = Math.max(0, parseFloat(cfg.margin_nota_kiri ?? 1.5) || 1.5);
    const bodySize = Math.max(6, parseInt(cfg.font_size_nota_body ?? 10, 10) || 10);
    const titleSize = Math.max(6, parseInt(cfg.font_size_nota_judul ?? 14, 10) || 14);
    const infoSize = Math.max(6, parseInt(cfg.font_size_nota_info ?? 10, 10) || 10);
    const tableSize = Math.max(6, parseInt(cfg.font_size_nota_tabel ?? 10, 10) || 10);
    const summarySize = Math.max(6, parseInt(cfg.font_size_nota_ringkasan ?? 10, 10) || 10);
    const footerSize = Math.max(6, parseInt(cfg.font_size_nota_footer ?? 9, 10) || 9);
    const escapeMap = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'};
    const escapeText = (text) => text ? String(text).replace(/[&<>"']/g, (c) => escapeMap[c] || c) : '';
    const formatNumber = (value) => 'Rp ' + Math.floor(value).toLocaleString('id-ID');

    const pageMargin = marginTop + 'mm ' + marginRight + 'mm ' + marginBottom + 'mm ' + marginLeft + 'mm';
    let html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Nota</title><style>@page{margin:' + pageMargin + '}body{font-family:"' + fontNota + '", monospace;width:' + width + 'mm;box-sizing:border-box;margin:0 auto;padding:' + pageMargin + ';font-size:' + bodySize + 'px;line-height:1.35}.header{text-align:center;margin-bottom:4mm}.header h2{margin:0;font-size:' + titleSize + 'px;line-height:1.15}.header p{margin:1px 0;font-size:' + infoSize + 'px;color:#444}.header .muted{color:#777;font-size:' + Math.max(6, infoSize - 2) + 'px}.header .custom{margin-top:3px;color:#333;font-size:' + infoSize + 'px;line-height:1.25}hr{margin:3px 0;border:none;border-top:1px solid #000}.info{font-size:' + infoSize + 'px;margin-bottom:3.5mm;line-height:1.35}table{width:100%;font-size:' + tableSize + 'px;border-collapse:collapse;margin-bottom:4mm}table thead tr{border-bottom:1px solid #000}table th{text-align:left;padding:2px 0;font-weight:bold;line-height:1.2}table td{padding:2px 0;line-height:1.2}table th:nth-child(2),table td:nth-child(2),table th:nth-child(3),table td:nth-child(3),table th:nth-child(4),table td:nth-child(4){text-align:right}table tbody tr{border-bottom:1px dotted #ccc}table .muted{color:#555;font-size:' + Math.max(6, tableSize - 2) + 'px;line-height:1.15}.summary{font-size:' + summarySize + 'px;margin-bottom:3.5mm;line-height:1.35}.summary-row{display:flex;justify-content:space-between;gap:6px}.total-row{border-top:1px solid #000;padding-top:2px;font-weight:bold}.footer{text-align:center;font-size:' + footerSize + 'px;color:#444;margin-top:4mm;line-height:1.3}@media print{body{margin:0 auto;padding:' + pageMargin + ';}}</style></head><body>';
    html += '<div class="header"><h2>' + escapeText(cfg.nama_toko || 'UD. BERSAUDARA') + '</h2>';
    const alamat = escapeText(cfg.alamat_toko || '');
    const telp = escapeText(cfg.nomor_telepon || '');
    const email = escapeText(cfg.email_toko || '');
    if (alamat) html += '<p class="muted">' + alamat + '</p>';
    if (telp) html += '<p class="muted">Telp: ' + telp + '</p>';
    if (email) html += '<p class="muted">Email: ' + email + '</p>';
    const headerLines = (cfg.custom_header_text || '').split(/\n+/).map((l) => l.trim()).filter(Boolean);
    if (headerLines.length) {
        html += '<div class="custom">';
        for (let i = 0; i < headerLines.length; i++) {
            html += '<div>' + escapeText(headerLines[i]) + '</div>';
        }
        html += '</div>';
    }
    html += '<hr></div>';
    const { tanggal, waktu } = getTanggalNota();
    html += '<div class="info"><div><strong>Tanggal:</strong> ' + tanggal;
    if ((cfg.tampilkan_jam ?? 1) === 1) {
        html += ' ' + waktu;
    }
    html += '</div>';
    const pembeliNama = document.getElementById('nama_pembeli').value.trim();
    if ((cfg.tampilkan_nama_pembeli ?? 1) === 1 && pembeliNama) {
        html += '<div><strong>Pembeli:</strong> ' + escapeText(pembeliNama) + '</div>';
    }
    html += '</div><table><thead><tr><th>Item</th><th>Jumlah</th><th>Harga</th><th>Total</th></tr></thead><tbody>';
    let totalHarga = 0, totalQty = 0, totalDiskon = 0, totalBruto = 0;
    for (let i = 0; i < items.length; i++) {
        const row = items[i];
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        const nama = row.querySelector('input[name*="[nama_barang]"]').value || 'Item';
        const kode = row.querySelector('input[name*="[kode_barang]"]').value || '';
        const satuan = row.querySelector('input[name*="[satuan]"]').value || '';
        const bruto = jumlah * harga;
        const subtotal = bruto - diskon;
        totalQty += jumlah;
        totalHarga += subtotal;
        totalDiskon += diskon;
        totalBruto += bruto;
        let itemCell = '<div>' + escapeText(nama.substring(0, 22)) + '</div>';
        if ((cfg.tampilkan_kode_barang ?? 1) === 1 && kode) {
            itemCell += '<div class="muted">Kode: ' + escapeText(kode) + '</div>';
        }
        if ((cfg.tampilkan_satuan ?? 1) === 1 && satuan) {
            itemCell += '<div class="muted">Satuan: ' + escapeText(satuan) + '</div>';
        }
        html += '<tr><td>' + itemCell + '</td><td>' + jumlah + '</td><td>' + formatNumber(harga) + '</td><td>' + formatNumber(subtotal) + '</td></tr>';
    }
    const uangDiberikan = parseNominalInput(document.getElementById('uang_diberikan').value);
    const kembalian = uangDiberikan - totalHarga;
    html += '</tbody></table><div class="summary">';
    html += '<div class="summary-row"><span><strong>Total Item:</strong></span><span>' + totalQty + '</span></div>';
    if ((cfg.jumlah_diskon_terpisah ?? 0) === 1) {
        html += '<div class="summary-row"><span><strong>Subtotal:</strong></span><span>' + formatNumber(totalBruto) + '</span></div>';
        html += '<div class="summary-row"><span><strong>Diskon:</strong></span><span>' + formatNumber(totalDiskon) + '</span></div>';
    }
    html += '<div class="summary-row"><span><strong>Total Harga:</strong></span><span>' + formatNumber(totalHarga) + '</span></div>';
    html += '<div class="summary-row"><span><strong>Uang Diberikan:</strong></span><span>' + formatNumber(uangDiberikan) + '</span></div>';

    const adaHutang = document.getElementById('ada_hutang').checked;
    if (adaHutang && (cfg.tampilkan_info_hutang ?? 1) === 1) {
        const namaPenghutang = document.getElementById('nama_penghutang').value.trim();
        const jatuhTempo = document.getElementById('jatuh_tempo').value;
        const jumlahHutang = parseFloat(document.getElementById('jumlah_hutang').value) || totalHarga;
        if (namaPenghutang) {
            html += '<div class="summary-row"><span><strong>Nama Hutang:</strong></span><span>' + escapeText(namaPenghutang) + '</span></div>';
        }
        if (jatuhTempo) {
            html += '<div class="summary-row"><span><strong>Jatuh Tempo:</strong></span><span>' + escapeText(jatuhTempo) + '</span></div>';
        }
        html += '<div class="summary-row"><span><strong>Jumlah Hutang:</strong></span><span>' + formatNumber(jumlahHutang) + '</span></div>';
    }

    html += '<div class="summary-row total-row"><span>Kembalian:</span><span>' + formatNumber(kembalian) + '</span></div>';
    html += '</div>';

    const footerLines = (cfg.custom_footer_text || '').split(/\n+/).map((l) => l.trim()).filter(Boolean);
    html += '<div class="footer">';
    if (cfg.footer_nota) {
        html += '<div>' + escapeText(cfg.footer_nota) + '</div>';
    }
    for (let i = 0; i < footerLines.length; i++) {
        html += '<div>' + escapeText(footerLines[i]) + '</div>';
    }
    html += '<div>' + escapeText(cfg.nama_toko || 'UD. BERSAUDARA') + '</div>';
    html += '</div></body></html>';
    const printWindow = window.open('', '', 'width=400,height=600');
    printWindow.document.write(html);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 250);
}

function initPenjualanEdit() {
    renderBarangList();
    handleUangDiberikanInput();

    if (existingDetails && existingDetails.length > 0) {
        for (let i = 0; i < existingDetails.length; i++) {
            addItemFromDetail(existingDetails[i]);
        }
        hitungTotal();
    }

    const searchInput = document.getElementById('search_barang_main');
    if (searchInput) {
        let searchTimeout = null;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => renderBarangList(e.target.value), 300);
        });
    }

    toggleHutangFields();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPenjualanEdit);
} else {
    initPenjualanEdit();
}

const namaPenghutangInput = document.getElementById('nama_penghutang');
const namaPembeliInput = document.getElementById('nama_pembeli');

if (namaPenghutangInput && namaPembeliInput) {
    namaPenghutangInput.addEventListener('input', function() {
        namaPembeliInput.value = this.value;
    });
}

const modalTambahStok = document.getElementById('modal_tambah_stok');
if (modalTambahStok) {
    modalTambahStok.addEventListener('click', function(e) {
        if (e.target === modalTambahStok) {
            closeTambahStokModal();
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modalTambahStok && !modalTambahStok.classList.contains('hidden')) {
        closeTambahStokModal();
    }
});
</script>

<?php
$content = ob_get_clean();
$title = 'Edit Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
