<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Tambah Pembelian Baru
    </h2>

    <form action="/pembelian/store" method="POST" id="formPembelian" onsubmit="return validateForm()">

        <!-- Panel Cari & Pilihan Barang -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Panel Daftar Barang Tersedia -->
            <div class="lg:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-700">Daftar Barang Tersedia</h3>
                        <button type="button" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 font-semibold"
                                onclick="openAddBarangModal()">
                            <i class="fas fa-plus mr-1"></i>Barang Baru
                        </button>
                    </div>
                    <input type="text" id="search_barang_main" placeholder="🔍 Cari nama/kode barang..." autocomplete="off"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 mb-3">
                </div>
                <div id="barang_list" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto">
                    <!-- Barang cards akan di-generate oleh JavaScript -->
                </div>
            </div>

            <!-- Panel Barang Dipilih -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-700">Barang Dipilih</h3>
                    <span id="selected_count" class="text-sm bg-blue-600 text-white px-2 py-1 rounded-full">0</span>
                </div>
                <div id="selected_container" class="space-y-2 max-h-80 overflow-y-auto"></div>
                <p id="no_items_msg" class="text-gray-500 text-center py-4 text-sm">Pilih barang dari daftar</p>
            </div>
        </div>

        <!-- Ringkasan Transaksi -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-gray-600 text-sm">Total Item</p>
                    <p class="text-2xl font-bold text-blue-600" id="total_items">0</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Subtotal</p>
                    <p class="text-2xl font-bold text-blue-600" id="subtotal_display">Rp 0</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Harga</p>
                    <p class="text-2xl font-bold text-green-600" id="total_display">Rp 0</p>
                </div>
            </div>
        </div>

        <!-- Info Supplier -->
        <div class="mb-6">
            <label for="nama_pembeli" class="block text-gray-700 font-semibold mb-2">Nama Supplier</label>
            <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Masukkan nama supplier..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Simpan Pembelian
            </button>
            <a href="/pembelian" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<!-- Modal Tambah Barang Baru -->
<div id="modal_add_barang" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
        <button class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" onclick="closeAddBarangModal()">
            <i class="fas fa-times"></i>
        </button>
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Tambah Barang Baru</h3>
        <form id="form_add_barang" onsubmit="submitAddBarang(event)">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Barang</label>
                <input type="text" name="kode_barang" placeholder="Misal: BRG-013" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Barang</label>
                <input type="text" name="nama_barang" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                <select name="id_kategori" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach (($kategori ?? []) as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Satuan</label>
                <select name="satuan" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    <option value="pcs">pcs</option>
                    <?php foreach (($satuanList ?? []) as $sat): ?>
                        <option value="<?= htmlspecialchars($sat['nama_satuan']) ?>"><?= htmlspecialchars($sat['nama_satuan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Beli</label>
                    <input type="number" name="harga_beli" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Harga Jual</label>
                    <input type="number" name="harga_jual" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg">Simpan & Tambah ke daftar</button>
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg" onclick="closeAddBarangModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = 0;
const allBarang = <?= json_encode($barang) ?>;
const kategoriOptions = <?= json_encode($kategori ?? []) ?>;
const satuanOptions = <?= json_encode($satuanList ?? []) ?>;

console.log('Data barang:', allBarang);

function renderBarangList(filterText = '') {
    const listDiv = document.getElementById('barang_list');
    if (!listDiv) return;
    
    const q = filterText.toLowerCase().trim();
    let filtered = [];
    
    // Filter barang
    for (let i = 0; i < allBarang.length; i++) {
        const b = allBarang[i];
        const nama = (b.nama_barang || '').toLowerCase().trim();
        const kode = (b.kode_barang || '').toLowerCase().trim();
        if (q === '' || nama.includes(q) || kode.includes(q)) {
            filtered.push(b);
        }
    }
    
    // Tampilkan hasil
    if (filtered.length === 0) {
        listDiv.innerHTML = `
            <div style="text-align:center;color:#999;padding:20px;grid-column:1/-1;">
                <p style="margin-bottom:15px;">Barang tidak ditemukan</p>
                <button type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700" onclick="openAddBarangModal()">
                    <i class="fas fa-plus mr-1"></i>Tambah Barang Baru
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    for (let i = 0; i < filtered.length; i++) {
        const item = filtered[i];
        const itemStr = JSON.stringify(item).replace(/"/g, '&quot;');
        html += `<div style="border:1px solid #ddd;border-radius:8px;padding:12px;background:#fff;cursor:pointer;transition: all 0.2s;" 
                    onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)';this.style.borderColor='#90caf9';" 
                    onmouseout="this.style.boxShadow='none';this.style.borderColor='#ddd';"
                    onclick='addItemFromBarang(${itemStr})'>
                    <div style="font-weight:bold;margin-bottom:8px;display:flex;justify-content:space-between;">
                        <div>${item.nama_barang}</div>
                        <span style="font-size:11px;background:#e3f2fd;color:#1976d2;padding:2px 6px;border-radius:3px;font-family:monospace;">${item.kode_barang}</span>
                    </div>
                    <div style="font-size:13px;color:#666;margin-bottom:8px;">
                        <div>Kategori: <strong>${item.nama_kategori || '-'}</strong></div>
                        <div>Stok: <strong>${item.stok}</strong> ${item.satuan}</div>
                        <div style="color:#2e7d32;font-weight:bold;">Harga Beli: Rp ${parseInt(item.harga_beli).toLocaleString('id-ID')}</div>
                    </div>
                </div>`;
    }
    listDiv.innerHTML = html;
}

function addItemFromBarang(barang) {
    const container = document.getElementById('selected_container');
    const noItemsMsg = document.getElementById('no_items_msg');
    
    // Check if barang sudah ada
    const existing = document.querySelector('[data-barang-id="' + barang.id_barang + '"]');
    if (existing) {
        const qtyInput = existing.querySelector('input[name*="[jumlah]"]');
        qtyInput.value = parseInt(qtyInput.value) + 1;
        hitungTotal();
        return;
    }
    
    let itemHtml = '<div style="border:1px solid #90caf9;background:#fff;border-radius:6px;padding:10px;margin-bottom:8px;" data-barang-id="' + barang.id_barang + '" data-item-index="' + itemIndex + '">';
    itemHtml += '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">';
    itemHtml += '<div><div style="font-weight:bold;font-size:13px;">' + barang.nama_barang + '</div>';
    itemHtml += '<span style="font-size:10px;background:#e3f2fd;color:#1976d2;padding:2px 6px;border-radius:2px;font-family:monospace;">' + barang.kode_barang + '</span></div>';
    itemHtml += '<button type="button" onclick="removeItem(' + itemIndex + ')" style="background:#f44336;color:white;border:none;padding:4px 8px;border-radius:3px;cursor:pointer;font-size:12px;">Hapus</button>';
    itemHtml += '</div>';
    itemHtml += '<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;font-size:12px;margin-bottom:8px;">';
    itemHtml += '<div><label style="display:block;color:#666;margin-bottom:4px;font-size:11px;">Jumlah</label>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][jumlah]" value="1" min="1" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:3px;font-size:12px;" onchange="hitungTotal()">';
    itemHtml += '</div>';
    itemHtml += '<div><label style="display:block;color:#666;margin-bottom:4px;font-size:11px;">Satuan</label>';
    itemHtml += '<div style="padding:6px;background:#f5f5f5;border:1px solid #ddd;border-radius:3px;font-size:12px;">' + barang.satuan + '</div></div>';
    itemHtml += '<div><label style="display:block;color:#666;margin-bottom:4px;font-size:11px;">Harga Beli</label>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][harga_satuan]" value="' + barang.harga_beli + '" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:3px;font-size:12px;" onchange="hitungTotal()">';
    itemHtml += '</div>';
    itemHtml += '<div><label style="display:block;color:#666;margin-bottom:4px;font-size:11px;">Harga Jual</label>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][harga_jual]" value="' + barang.harga_jual + '" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:3px;font-size:12px;color:#2e7d32;font-weight:bold;">';
    itemHtml += '</div>';
    itemHtml += '</div>';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][id_barang]" value="' + barang.id_barang + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][diskon]" value="0">';
    itemHtml += '</div>';
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    noItemsMsg.style.display = 'none';
    itemIndex++;
    hitungTotal();
}

function removeItem(idx) {
    const row = document.querySelector('[data-item-index="' + idx + '"]');
    if (row) row.remove();
    
    const container = document.getElementById('selected_container');
    if (container.children.length === 0) {
        document.getElementById('no_items_msg').style.display = 'block';
    }
    hitungTotal();
}

function hitungTotal() {
    let totalItems = 0;
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-barang-id]');
    
    rows.forEach(row => {
        const jumlah = parseInt(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const subtotal = jumlah * harga;
        totalItems += jumlah;
        totalHarga += subtotal;
    });
    
    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('subtotal_display').textContent = 'Rp ' + totalHarga.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    document.getElementById('total_display').textContent = 'Rp ' + totalHarga.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    document.getElementById('selected_count').textContent = totalItems;
}

function validateForm() {
    const items = document.querySelectorAll('[data-barang-id]');
    if (items.length === 0) {
        alert('Tambahkan minimal satu barang!');
        return false;
    }
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
    const formData = new FormData(form);
    
    // Tambah default jumlah = 1
    formData.append('stok', '1');
    
    try {
        const resp = await fetch('/api/barang/store', { method: 'POST', body: formData });
        const data = await resp.json();
        
        if (!data.success) {
            alert(data.message || 'Gagal menambahkan barang baru');
            return;
        }
        
        const barangBaru = data.barang;
        allBarang.push(barangBaru);
        addItemFromBarang(barangBaru);
        form.reset();
        closeAddBarangModal();
        document.getElementById('search_barang_main').value = '';
        renderBarangList('');
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat menambahkan barang');
    }
}

// Setup search
document.getElementById('search_barang_main').addEventListener('input', function(e) {
    renderBarangList(e.target.value);
});

// Init
renderBarangList('');
</script>

<?php 
$content = ob_get_clean();
$title = 'Tambah Pembelian - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
