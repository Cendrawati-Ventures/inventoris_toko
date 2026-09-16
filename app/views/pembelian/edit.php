<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
        <i class="fas fa-edit text-yellow-600"></i>
        Edit Barang Masuk
    </h2>
    <p class="text-gray-600 mb-6">Hanya menampilkan item yang sudah dibeli untuk diedit tanpa panel pencarian baru.</p>

    <form action="/pembelian/update/<?= $pembelian['id_pembelian'] ?>" method="POST" id="formPembelian" onsubmit="return validateForm()">
        <div class="mb-6">
            <label for="tanggal_masuk" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Barang Masuk *</label>
            <input type="date" id="tanggal_masuk" name="tanggal" required value="<?= htmlspecialchars(substr((string)$pembelian['tanggal'], 0, 10)) ?>" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">
            <p class="text-xs text-slate-500 mt-1">Pilih tanggal barang benar-benar masuk, termasuk hari sebelumnya. Stok saat ini tetap memperhitungkan penjualan setelah tanggal tersebut.</p>
        </div>
        <!-- Info Supplier -->
        <div class="mb-6">
            <label for="nama_pembeli" class="block text-gray-700 font-semibold mb-2">Nama Supplier</label>
            <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Masukkan nama supplier..."
                   value="<?= htmlspecialchars($pembelian['nama_pembeli'] ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <!-- Daftar Item Pembelian -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-700">Item Barang Masuk</h3>
                <span id="selected_count" class="text-sm text-gray-500"></span>
            </div>

            <div id="selected_container" class="space-y-4"></div>
            <p id="no_items_msg" class="text-gray-500 text-center py-3 hidden">Tidak ada item untuk diedit.</p>
        </div>

        <!-- Ringkasan Transaksi -->
        <?php 
            $totalItemsAwal = 0;
            $totalHargaAwal = 0;
            foreach ($details as $item) {
                $totalItemsAwal += (float)($item['jumlah'] ?? 0);
                $totalHargaAwal += ((float)($item['jumlah'] ?? 0) * (float)($item['harga_satuan'] ?? 0));
            }
        ?>
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-gray-600 text-sm">Total Item</p>
                    <p class="text-2xl font-bold text-blue-600" id="total_items"><?= $totalItemsAwal ?></p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Barang Masuk</p>
                    <p class="text-2xl font-bold text-green-600" id="total_display">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Simpan Perubahan
            </button>
            <a href="/pembelian" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<script src="/assets/js/money.js"></script>
<script>
let itemIndex = 0;
const existingDetails = <?= json_encode($details) ?>;
const allBarang = <?= json_encode($barang ?? []) ?>;
const satuanList = <?= json_encode($satuanList ?? []) ?>;

function toDigitOnly(value) {
    return String(value ?? '').replace(/[^\d]/g, '');
}

function normalizeMoneyValue(value) {
    return MoneyID.parse(value);
}

function formatThousandID(value) {
    return MoneyID.format(value);
}

function parseCurrencyValue(value) {
    return normalizeMoneyValue(value);
}

function formatRupiah(value) {
    const number = Number(value) || 0;
    return 'Rp ' + Math.floor(number).toLocaleString('id-ID');
}

function markPricePairInvalid(hargaBeliInput, hargaJualInput, invalid) {
    [hargaBeliInput, hargaJualInput].forEach((input) => {
        if (!input) return;
        input.classList.toggle('border-red-400', invalid);
        input.classList.toggle('ring-2', invalid);
        input.classList.toggle('ring-red-100', invalid);
    });
}

function getBarangUnitOptions(barang) {
    const rawOptions = Array.isArray(barang && barang.satuan_detail) && barang.satuan_detail.length > 0
        ? barang.satuan_detail
        : [{ satuan: barang?.satuan || 'pcs', harga_beli: barang?.harga_beli || 0, harga_jual: barang?.harga_jual || 0 }];

    return rawOptions.map((unit) => ({
        satuan: String(unit?.satuan || barang?.satuan || 'pcs').trim() || (barang?.satuan || 'pcs'),
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

function syncDetailUnitPreview(select, idx) {
    if (!select) return;
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (!row) return;
    const barangId = select.getAttribute('data-barang-id');
    const barang = allBarang.find((item) => String(item.id_barang) === String(barangId));
    if (!barang) return;
    const selected = resolveBarangSelection(barang, select.value);
    const beliInput = row.querySelector('input[name*="[harga_satuan]"]');
    const jualInput = row.querySelector('input[name*="[harga_jual]"]');
    if (beliInput) beliInput.value = formatThousandID(selected.harga_beli || 0);
    if (jualInput) jualInput.value = formatThousandID(selected.harga_jual || 0);
    updateItemSubtotal(idx);
    hitungTotal();
}

function appendItem(detail) {
    const container = document.getElementById('selected_container');
    const noItemsMsg = document.getElementById('no_items_msg');
    const barang = allBarang.find((item) => String(item.id_barang) === String(detail.id_barang)) || null;
    const unitOptions = getBarangUnitOptions(barang);
    const selectedSatuan = String(detail.satuan || (unitOptions[0]?.satuan || barang?.satuan || 'pcs')).trim();
    const chosenUnit = resolveBarangSelection(barang, selectedSatuan);
    const defaultHargaBeli = Number(detail.harga_satuan || chosenUnit.harga_beli || barang?.harga_beli || 0);
    const defaultHargaJual = Number(chosenUnit.harga_jual || barang?.harga_jual || 0);

    const rowHtml = `
        <div class="bg-white border border-gray-200 rounded-lg p-4 selected-row" data-item-index="${itemIndex}">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <p class="font-semibold text-gray-800">${detail.nama_barang}</p>
                    <p class="text-sm text-gray-600">${detail.satuan ? 'Satuan: ' + detail.satuan : ''}</p>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeItem(${itemIndex})" aria-label="Hapus item">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>

            <input type="hidden" name="items[${itemIndex}][id_barang]" value="${detail.id_barang}">
            <input type="hidden" name="items[${itemIndex}][diskon]" value="0">

            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                    <select name="items[${itemIndex}][satuan]" data-barang-id="${detail.id_barang}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm" onchange="syncDetailUnitPreview(this, ${itemIndex})">
                        <option value="">-- Pilih Satuan --</option>
                        ${(unitOptions.length ? unitOptions : [{ satuan: selectedSatuan, harga_beli: defaultHargaBeli, harga_jual: defaultHargaJual }]).map((unit) => `<option value="${unit.satuan}" ${unit.satuan === selectedSatuan ? 'selected' : ''}>${unit.satuan}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Jumlah</label>
                    <input type="number" name="items[${itemIndex}][jumlah]" value="${detail.jumlah}" min="1" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"
                           onchange="hitungTotal()" onkeyup="hitungTotal()">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Harga Beli</label>
                    <input type="text" name="items[${itemIndex}][harga_satuan]" value="${formatThousandID(defaultHargaBeli)}" data-price-input min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"
                           onchange="hitungTotal(); markPricePairInvalid(this, document.querySelector('[data-item-index=\"'+${itemIndex}+'\"] input[name*=\"[harga_jual]\"]'), parseCurrencyValue(this.value) > 0 && parseCurrencyValue(document.querySelector('[data-item-index=\"'+${itemIndex}+'\"] input[name*=\"[harga_jual]\"]').value) > 0 && parseCurrencyValue(this.value) >= parseCurrencyValue(document.querySelector('[data-item-index=\"'+${itemIndex}+'\"] input[name*=\"[harga_jual]\"]').value));">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Harga Jual</label>
                    <input type="text" name="items[${itemIndex}][harga_jual]" value="${formatThousandID(defaultHargaJual)}" data-price-input min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"
                           onchange="hitungTotal(); markPricePairInvalid(document.querySelector('[data-item-index=\"'+${itemIndex}+'\"] input[name*=\"[harga_satuan]\"]'), this, parseCurrencyValue(document.querySelector('[data-item-index=\"'+${itemIndex}+'\"] input[name*=\"[harga_satuan]\"]').value) > 0 && parseCurrencyValue(this.value) > 0 && parseCurrencyValue(document.querySelector('[data-item-index=\"'+${itemIndex}+'\"] input[name*=\"[harga_satuan]\"]').value) >= parseCurrencyValue(this.value));">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Subtotal</label>
                    <div class="subtotal w-full px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm font-bold text-green-700">
                        Rp ${parseInt(detail.subtotal || (detail.jumlah * defaultHargaBeli) || 0).toLocaleString('id-ID')}
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', rowHtml);
    noItemsMsg.classList.add('hidden');
    itemIndex++;
    hitungTotal();
}

function removeItem(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (row) {
        row.remove();
        if (document.querySelectorAll('.selected-row').length === 0) {
            document.getElementById('no_items_msg').classList.remove('hidden');
        }
        hitungTotal();
    }
}

function hitungTotal() {
    let totalItems = 0;
    let totalHarga = 0;

    document.querySelectorAll('.selected-row').forEach(row => {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseCurrencyValue(row.querySelector('input[name*="[harga_satuan]"]').value);
        const subtotal = jumlah * harga;

        totalItems += jumlah;
        totalHarga += subtotal;

        const subtotalEl = row.querySelector('.subtotal');
        if (subtotalEl) subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', { maximumFractionDigits: 0 });

        const hargaJualInput = row.querySelector('input[name*="[harga_jual]"]');
        const hargaBeliInput = row.querySelector('input[name*="[harga_satuan]"]');
        const hargaJual = parseCurrencyValue(hargaJualInput?.value);
        const invalidPair = harga > 0 && hargaJual > 0 && harga >= hargaJual;
        markPricePairInvalid(hargaBeliInput, hargaJualInput, invalidPair);
    });

    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('total_display').textContent = 'Rp ' + totalHarga.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    document.getElementById('selected_count').textContent = document.querySelectorAll('.selected-row').length + ' item';
}

function validateForm() {
    const items = document.querySelectorAll('.selected-row');
    if (items.length === 0) {
        alert('Tidak ada item untuk disimpan.');
        return false;
    }

    for (const row of items) {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const hargaBeliInput = row.querySelector('input[name*="[harga_satuan]"]');
        const hargaJualInput = row.querySelector('input[name*="[harga_jual]"]');
        const hargaBeli = parseCurrencyValue(hargaBeliInput?.value);
        const hargaJual = parseCurrencyValue(hargaJualInput?.value);

        if (jumlah <= 0) {
            alert('Jumlah tiap item harus lebih dari 0.');
            return false;
        }
        if (hargaBeli <= 0 || hargaJual <= 0) {
            alert('Harga beli dan harga jual tiap item harus terisi.');
            return false;
        }
        if (hargaBeli >= hargaJual) {
            alert('Harga beli harus lebih kecil dari harga jual untuk setiap satuan item.');
            return false;
        }
    }
    return true;
}

window.addEventListener('load', () => {
    if (existingDetails && existingDetails.length > 0) {
        existingDetails.forEach(detail => appendItem(detail));
    }
    if (document.querySelectorAll('.selected-row').length === 0) {
        document.getElementById('no_items_msg').classList.remove('hidden');
    }
    hitungTotal();
});
</script>

<?php 
$content = ob_get_clean();
$title = 'Edit Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
