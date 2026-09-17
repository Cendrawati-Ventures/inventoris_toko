<?php ob_start(); ?>
<?php
$showUpdateAlert = isset($_GET['updated']) && $_GET['updated'] === '1';
$stokChanged = isset($_GET['stok_changed']) && $_GET['stok_changed'] === '1';
$stokBefore = isset($_GET['stok_before']) ? (int)$_GET['stok_before'] : null;
$stokAfter = isset($_GET['stok_after']) ? (int)$_GET['stok_after'] : null;
?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 max-w-4xl mx-auto">
    <?php if ($showUpdateAlert): ?>
        <div class="mb-6 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 py-3">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="fas fa-check"></i>
                </span>
                <div class="flex-1">
                    <p class="text-sm font-bold text-emerald-800">Perubahan berhasil disimpan</p>
                    <?php if ($stokChanged && $stokBefore !== null && $stokAfter !== null): ?>
                        <p class="mt-1 text-sm text-emerald-700">
                            Stok diperbarui dari <span class="font-semibold"><?= number_format($stokBefore, 0, ',', '.') ?></span>
                            menjadi <span class="font-semibold"><?= number_format($stokAfter, 0, ',', '.') ?></span>.
                        </p>
                    <?php else: ?>
                        <p class="mt-1 text-sm text-emerald-700">Data barang telah diperbarui. Silakan cek kembali sebelum lanjut.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-8 flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-yellow-600">Produk</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-800">
                <i class="fas fa-edit text-yellow-600 mr-2"></i>Edit Barang
            </h2>
        </div>
        <span class="inline-flex items-center rounded-full bg-yellow-50 px-3 py-1 text-xs font-semibold text-yellow-700 border border-yellow-100">
            Update
        </span>
    </div>

    <form action="/barang/update/<?= $barang['id_barang'] ?>" method="POST" id="formBarangEdit" class="space-y-6">
        <div class="mb-6">
            <label for="kode_barang" class="block text-gray-700 font-bold mb-2 text-sm">Kode Barang *</label>
            <input type="text" id="kode_barang" name="kode_barang" required
                   value="<?= htmlspecialchars($barang['kode_barang']) ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="nama_barang" class="block text-gray-700 font-bold mb-2 text-sm">Nama Barang *</label>
            <input type="text" id="nama_barang" name="nama_barang" required
                   value="<?= htmlspecialchars($barang['nama_barang']) ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="kategori" class="block text-gray-700 font-bold mb-2 text-sm">Kategori *</label>
            <select id="kategori" name="id_kategori" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori as $kat): ?>
                    <option value="<?= $kat['id_kategori'] ?>" <?= $barang['id_kategori'] == $kat['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-amber-50 p-4 sm:p-5 shadow-[0_12px_30px_rgba(15,23,42,0.04)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <label class="block text-slate-800 font-bold text-sm">Satuan & Harga</label>
                    <p class="mt-1 text-xs text-slate-500">Atur satuan, harga beli, dan harga jual untuk setiap unit barang.</p>
                </div>
                <button type="button" id="btnAddSatuanDetail" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-3 py-2 text-sm font-semibold shadow-sm transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i>Tambah Satuan
                </button>
            </div>

            <div class="mb-3 grid grid-cols-6 gap-2 px-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 md:grid-cols-[1.15fr_0.8fr_1.2fr_1.2fr_0.9fr_0.45fr]">
                <span class="truncate">Satuan</span>
                <span class="truncate">Isi</span>
                <span class="truncate">Harga Beli</span>
                <span class="truncate">Harga Jual</span>
                <span class="truncate">Keuntungan</span>
                <span class="text-center truncate">Hapus</span>
            </div>

            <div id="satuan_detail_container" class="space-y-3"></div>
            <input type="hidden" name="satuan_detail" id="satuan_detail_input" value='<?= htmlspecialchars(json_encode($barang['satuan_detail'] ?? []), ENT_QUOTES, 'UTF-8') ?>'>
            <input type="hidden" name="satuan" id="default_satuan" value="<?= htmlspecialchars((string)($barang['satuan'] ?? 'pcs')) ?>">
        </div>

        <p id="price_notice" class="hidden -mt-1 mb-2 text-sm text-red-600 font-semibold">
            Harga jual harus selalu lebih tinggi dari harga beli.
        </p>

        <div class="mb-8">
            <label for="stok" class="block text-gray-700 font-bold mb-2 text-sm">Stok</label>
            <input type="number" id="stok" name="stok" required min="0"
                   value="<?= $barang['stok'] ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-8">
            <label for="tanggal_expired" class="block text-gray-700 font-bold mb-2 text-sm">Tanggal Expired</label>
            <input type="date" id="tanggal_expired" name="tanggal_expired"
                   value="<?= !empty($barang['tanggal_expired']) ? htmlspecialchars(date('Y-m-d', strtotime($barang['tanggal_expired']))) : '' ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ada masa expired.</p>
        </div>

        <div class="flex gap-4 justify-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                <i class="fas fa-save mr-2"></i>Update
            </button>
            <a href="/barang" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<script src="/assets/js/money.js"></script>
<script>
const SATUAN_OPTIONS = <?= json_encode(array_values(array_map(function ($item) {
    return (string)($item['nama_satuan'] ?? '');
}, $satuan ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function buildSatuanSelect(selectedValue = '') {
    const values = [...new Set([...SATUAN_OPTIONS, selectedValue || 'pcs'].filter((value) => String(value).trim() !== ''))];

    return `
        <select data-field="satuan" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm" aria-label="Pilih satuan barang">
            <option value="">Pilih satuan</option>
            ${values.map((value) => `
                <option value="${escapeHtml(value)}" ${String(value) === String(selectedValue || '') ? 'selected' : ''}>
                    ${escapeHtml(value)}
                </option>
            `).join('')}
        </select>
    `;
}

function toDigitOnly(value) {
    return String(value ?? '').replace(/[^\d]/g, '');
}

function normalizeMoneyValue(value) {
    return MoneyID.parse(value);
}

function formatThousandID(value) {
    return MoneyID.format(value);
}

function calculateProfitPercent(hargaBeli, hargaJual) {
    const beli = Number(hargaBeli || 0);
    const jual = Number(hargaJual || 0);
    if (!beli || !jual) return 0;
    return Number(((jual - beli) / beli) * 100).toFixed(2);
}

function getRowValidation(row) {
    const satuanInput = row.querySelector('[data-field="satuan"]');
    const nilaiInput = row.querySelector('[data-field="nilai"]');
    const beliInput = row.querySelector('[data-field="harga_beli"]');
    const jualInput = row.querySelector('[data-field="harga_jual"]');
    const nilai = Number(parseFloat((nilaiInput?.value || '1').replace(/[^\d.]/g, '')) || 1);
    const beli = normalizeMoneyValue(beliInput?.value || 0);
    const jual = normalizeMoneyValue(jualInput?.value || 0);
    const hasAnyValue = (satuanInput?.value.trim() || '').length > 0 || nilai > 0 || beli > 0 || jual > 0;
    const invalid = beli > 0 && jual > 0 && jual <= beli;
    const profit = calculateProfitPercent(beli, jual);

    return { satuanInput, nilaiInput, beliInput, jualInput, nilai, beli, jual, hasAnyValue, invalid, profit };
}

function syncSatuanDetailState() {
    const container = document.getElementById('satuan_detail_container');
    if (!container) return true;

    const rows = [...container.querySelectorAll('[data-satuan-row]')];
    const detail = rows.map((row) => {
        const { satuanInput, nilai, beli, jual, hasAnyValue } = getRowValidation(row);
        const satuan = (satuanInput?.value || '').trim();
        if (!hasAnyValue || !satuan) return null;
        return { satuan, nilai: Number.isFinite(nilai) && nilai > 0 ? nilai : 1, harga_beli: beli, harga_jual: jual };
    }).filter(Boolean);

    const hidden = document.getElementById('satuan_detail_input');
    if (hidden) hidden.value = JSON.stringify(detail);

    const defaultRow = detail.find(unit => Number(unit.nilai) === 1) || detail[0] || { satuan: '', harga_beli: 0, harga_jual: 0 };
    const defaultSatuanInput = document.getElementById('default_satuan');
    if (defaultSatuanInput) defaultSatuanInput.value = defaultRow.satuan;

    const notice = document.getElementById('price_notice');
    let hasInvalidRows = false;

    rows.forEach((row) => {
        const { invalid, profit, beli, jual } = getRowValidation(row);
        const resultLabel = row.querySelector('[data-field="price-status"]');
        const profitValue = row.querySelector('[data-field="profit_percent"]');

        if (resultLabel) {
            resultLabel.textContent = invalid
                ? 'Harga jual harus di atas harga beli'
                : (beli > 0 && jual > 0 ? `Keuntungan ${profit}%` : 'Belum ada data harga');
            resultLabel.className = 'mt-2 block text-xs font-semibold rounded-full px-2.5 py-1 ' + (
                invalid ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'
            );
        }

        if (profitValue) {
            profitValue.textContent = `${profit}%`;
            profitValue.className = 'text-sm font-semibold ' + (invalid ? 'text-red-600' : 'text-emerald-700');
        }

        const rowWrap = row.querySelector('[data-row-body]');
        if (rowWrap) {
            rowWrap.classList.toggle('border-red-300', invalid);
            rowWrap.classList.toggle('bg-red-50', invalid);
        }

        hasInvalidRows = hasInvalidRows || invalid;
    });

    if (notice) {
        notice.textContent = 'Setiap satuan harus memiliki harga jual di atas harga beli.';
        notice.classList.toggle('hidden', !hasInvalidRows);
    }

    if (rows.length === 0) {
        addSatuanDetailRow();
    }

    return !hasInvalidRows;
}

function addSatuanDetailRow(rowData = { satuan: SATUAN_OPTIONS[0] || '', harga_beli: 0, harga_jual: 0, nilai: 1 }) {
    const container = document.getElementById('satuan_detail_container');
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'rounded-2xl border border-slate-200 bg-white/90 shadow-[0_8px_18px_rgba(15,23,42,0.04)] backdrop-blur-sm transition-all duration-200 hover:shadow-[0_10px_24px_rgba(59,130,246,0.08)]';
    row.dataset.satuanRow = 'true';
    const selectedSatuan = rowData.satuan || SATUAN_OPTIONS[0] || 'pcs';
    row.innerHTML = `
        <div data-row-body class="grid grid-cols-1 md:grid-cols-[1.15fr_0.8fr_1.2fr_1.2fr_0.9fr_0.45fr] gap-2 p-3">
            ${buildSatuanSelect(selectedSatuan)}
            <input type="number" min="1" step="1" data-field="nilai" placeholder="1" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm" value="${rowData.nilai || 1}">
            <input type="text" data-field="harga_beli" placeholder="Harga beli" data-price-input inputmode="decimal" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm" value="${formatThousandID(rowData.harga_beli || 0)}">
            <input type="text" data-field="harga_jual" placeholder="Harga jual" data-price-input inputmode="decimal" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm" value="${formatThousandID(rowData.harga_jual || 0)}">
            <div class="flex h-[46px] items-center justify-center rounded-xl bg-emerald-50 border border-emerald-200 px-3 text-center shadow-sm">
                <span data-field="profit_percent" class="text-sm font-semibold text-emerald-700">0.00%</span>
            </div>
            <div class="flex items-center justify-center">
                <button type="button" class="remove-satuan-row flex h-[46px] w-[46px] items-center justify-center rounded-xl border border-red-200 bg-red-50 text-lg font-bold text-red-600 hover:bg-red-100 shadow-sm transition" title="Hapus satuan">×</button>
            </div>
        </div>
        <div class="px-3 pb-3">
            <span data-field="price-status" class="mt-2 block text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 px-2.5 py-1 inline-block">Belum ada data harga</span>
        </div>
    `;

    const beliInput = row.querySelector('[data-field="harga_beli"]');
    const jualInput = row.querySelector('[data-field="harga_jual"]');
    const satuanInput = row.querySelector('[data-field="satuan"]');
    const nilaiInput = row.querySelector('[data-field="nilai"]');

    [beliInput, jualInput].forEach((input) => {
        input.addEventListener('input', () => {
            input.value = MoneyID.formatInput(input.value);
            syncSatuanDetailState();
        });
    });

    if (nilaiInput) {
        nilaiInput.addEventListener('input', syncSatuanDetailState);
    }
    if (satuanInput) {
        satuanInput.addEventListener('change', syncSatuanDetailState);
    }
    row.querySelector('.remove-satuan-row').addEventListener('click', () => {
        row.remove();
        syncSatuanDetailState();
    });

    container.appendChild(row);
    syncSatuanDetailState();
}

function bindPriceInputFormatting(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const priceInputs = form.querySelectorAll('[data-price-input]');
    const validatePricePair = () => {
        const notice = form.querySelector('#price_notice');
        if (notice) {
            notice.textContent = 'Harga jual harus selalu lebih tinggi dari harga beli.';
            notice.classList.toggle('hidden', false);
        }
        return true;
    };

    priceInputs.forEach((input) => {
        input.addEventListener('input', () => {
            input.value = MoneyID.formatInput(input.value);
            const caretPosition = input.value.length;
            requestAnimationFrame(() => {
                try {
                    input.setSelectionRange(caretPosition, caretPosition);
                } catch (e) {}
            });
            validatePricePair();
            syncSatuanDetailState();
        });
    });

    form.addEventListener('submit', (event) => {
        const wholeFormValid = validatePricePair() && syncSatuanDetailState();
        if (!wholeFormValid) {
            event.preventDefault();
            const firstInvalidInput = form.querySelector('.border-red-400, .bg-red-50');
            if (firstInvalidInput) firstInvalidInput.focus();
            return;
        }

        priceInputs.forEach((input) => {
            input.value = String(MoneyID.parse(input.value));
        });
        syncSatuanDetailState();
    });
}

bindPriceInputFormatting('formBarangEdit');
const baseSatuanDetail = <?= json_encode($barang['satuan_detail'] ?? []) ?>;
const addBtn = document.getElementById('btnAddSatuanDetail');
if (addBtn) {
    addBtn.addEventListener('click', () => addSatuanDetailRow());
}
if (Array.isArray(baseSatuanDetail) && baseSatuanDetail.length > 0) {
    baseSatuanDetail.forEach((item) => addSatuanDetailRow({
        satuan: item.satuan || '',
        nilai: Number(item.nilai || item.nilai_satuan || item.konversi || 1),
        harga_beli: Number(item.harga_beli || 0),
        harga_jual: Number(item.harga_jual || 0)
    }));
} else {
    addSatuanDetailRow({ satuan: '<?= htmlspecialchars((string)($barang['satuan'] ?? '')) ?>', harga_beli: Number(<?= (float)$barang['harga_beli'] ?>), harga_jual: Number(<?= (float)$barang['harga_jual'] ?>) });
}
</script>

<?php 
$content = ob_get_clean();
$title = 'Edit Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
