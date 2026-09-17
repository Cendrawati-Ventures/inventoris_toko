<script>
(() => {
const editorFormId = <?= json_encode($barangFormId ?? 'formBarangCreate') ?>;
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
    if (defaultSatuanInput) defaultSatuanInput.value = defaultRow.satuan || '';
    document.getElementById('default_harga_beli').value = defaultRow.harga_beli || 0;
    document.getElementById('default_harga_jual').value = defaultRow.harga_jual || 0;

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

    const seen = new Set();
    let message = '';
    for (const unit of detail) {
        const key = unit.satuan.toLowerCase();
        if (seen.has(key)) message = 'Satuan tidak boleh berulang.';
        seen.add(key);
        if (!Number.isInteger(unit.nilai) || unit.nilai < 1) message = 'Isi satuan harus bilangan bulat minimal 1.';
        if (unit.harga_beli < 0 || unit.harga_jual <= unit.harga_beli) message = 'Harga jual setiap satuan harus lebih tinggi dari harga beli.';
    }
    if (!detail.some(unit => unit.nilai === 1)) message = 'Tambahkan satuan dasar dengan isi 1.';
    if (detail.length !== rows.length) message = 'Pilih satuan pada semua baris.';
    if (message && notice) {
        notice.textContent = message;
        notice.classList.remove('hidden');
    }
    return !hasInvalidRows && !message;
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

const addBtn = document.getElementById('btnAddSatuanDetail');
if (addBtn) {
    addBtn.addEventListener('click', () => addSatuanDetailRow());
}

bindPriceInputFormatting(editorFormId);
addSatuanDetailRow({ satuan: SATUAN_OPTIONS[0] || 'pcs', harga_beli: 0, harga_jual: 0, nilai: 1 });
window.barangFormEditors = window.barangFormEditors || {};
window.barangFormEditors[editorFormId] = {
    validate: syncSatuanDetailState,
    reset() {
        document.getElementById(editorFormId).reset();
        document.getElementById('satuan_detail_container').innerHTML = '';
        addSatuanDetailRow({ satuan: 'pcs', nilai: 1, harga_beli: 0, harga_jual: 0 });
    }
};
})();
</script>