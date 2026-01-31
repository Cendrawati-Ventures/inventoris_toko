<?php ob_start(); ?>

<div class="container mx-auto px-4 py-8 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Kategori & Satuan</h1>
        </div>
        <div class="grid grid-cols-2 gap-3 min-w-[280px]">
            <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 text-blue-700">
                <p class="text-xs uppercase tracking-wide">Total Kategori</p>
                <p class="text-xl font-bold"><?= count($kategori) ?></p>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-lg px-4 py-3 text-green-700">
                <p class="text-xs uppercase tracking-wide">Total Satuan</p>
                <p class="text-xl font-bold"><?= count($satuan) ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Kategori Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Kategori Produk</h2>
                </div>
                <span class="text-xs px-3 py-1 bg-blue-50 text-blue-700 rounded-full border border-blue-100"><?= count($kategori) ?> tercatat</span>
            </div>

            <form method="POST" action="/setting/kategori/add" class="mb-8">
                <div class="mb-4">
                    <label for="nama_kategori" class="block text-gray-700 font-semibold mb-2">
                        Tambah Kategori Baru
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            id="nama_kategori"
                            name="nama_kategori"
                            placeholder="Nama kategori (mis: Elektronik, Minuman, dll)"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm"
                            required
                        >
                        <button
                            type="submit"
                            class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow"
                        >
                            Tambah
                        </button>
                    </div>
                </div>
            </form>

            <div class="border-t pt-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Daftar Kategori</h3>
                    <input type="text" placeholder="Cari kategori..." onkeyup="filterList(this, 'kategori-list')"
                           class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div id="kategori-list" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php if (empty($kategori)): ?>
                        <p class="text-gray-500 text-sm">Belum ada kategori</p>
                    <?php else: ?>
                        <?php foreach ($kategori as $item): ?>
                            <div class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-lg px-3 py-2 shadow-sm">
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_kategori']) ?></p>
                                    <p class="text-[11px] text-gray-500">Kategori</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-xs px-3 py-1 bg-white border border-blue-200 text-blue-700 rounded hover:bg-blue-50" onclick="editItem('kategori', <?= $item['id_kategori'] ?>, '<?= htmlspecialchars($item['nama_kategori'], ENT_QUOTES) ?>')">Edit</button>
                                    <button type="button" class="text-xs px-3 py-1 bg-red-50 border border-red-200 text-red-700 rounded hover:bg-red-100" onclick="deleteItem('kategori', <?= $item['id_kategori'] ?>)">Hapus</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Satuan Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Satuan Produk</h2>
                </div>
                <span class="text-xs px-3 py-1 bg-green-50 text-green-700 rounded-full border border-green-100"><?= count($satuan) ?> tercatat</span>
            </div>

            <form method="POST" action="/setting/satuan/add" class="mb-8">
                <div class="mb-4">
                    <label for="nama_satuan" class="block text-gray-700 font-semibold mb-2">
                        Tambah Satuan Baru
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            id="nama_satuan"
                            name="nama_satuan"
                            placeholder="Nama satuan (mis: pcs, kg, liter, dll)"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
                            required
                        >
                        <button
                            type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow"
                        >
                            Tambah
                        </button>
                    </div>
                </div>
            </form>

            <div class="border-t pt-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Daftar Satuan</h3>
                    <input type="text" placeholder="Cari satuan..." onkeyup="filterList(this, 'satuan-list')"
                           class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>
                <div id="satuan-list" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <?php if (empty($satuan)): ?>
                        <p class="text-gray-500 text-sm">Belum ada satuan</p>
                    <?php else: ?>
                        <?php foreach ($satuan as $item): ?>
                            <div class="flex items-center justify-between bg-gradient-to-r from-green-50 to-white border border-green-100 rounded-lg px-3 py-2 shadow-sm">
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_satuan']) ?></p>
                                    <p class="text-[11px] text-gray-500">Satuan</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-xs px-3 py-1 bg-white border border-green-200 text-green-700 rounded hover:bg-green-50" onclick="editItem('satuan', <?= $item['id_satuan'] ?>, '<?= htmlspecialchars($item['nama_satuan'], ENT_QUOTES) ?>')">Edit</button>
                                    <button type="button" class="text-xs px-3 py-1 bg-red-50 border border-red-200 text-red-700 rounded hover:bg-red-100" onclick="deleteItem('satuan', <?= $item['id_satuan'] ?>)">Hapus</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg p-4 text-sm text-blue-800 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <h3 class="font-semibold text-blue-900 mb-1">Tips penamaan</h3>
            <p>Gunakan nama singkat dan jelas. Contoh:  “Minuman”, “Rokok”, “pcs”, “botol”.</p>
        </div>
        <div class="flex items-center gap-2 text-blue-900 font-semibold">
            <span class="text-xs px-2 py-1 bg-white border border-blue-200 rounded">Cek duplikat sebelum menambahkan kategori</span>
        </div>
    </div>
</div>

<script>
function filterList(input, listId) {
    const term = input.value.toLowerCase();
    const list = document.getElementById(listId);
    if (!list) return;
    list.querySelectorAll('div').forEach(item => {
        const text = item.innerText.toLowerCase();
        item.classList.toggle('hidden', !text.includes(term));
    });
}

function submitHiddenForm(action, payload) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;
    Object.keys(payload).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = payload[key];
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
}

function editItem(type, id, currentName) {
    const newName = prompt('Ubah ' + type + ' ke:', currentName);
    if (newName === null) return;
    const trimmed = newName.trim();
    if (!trimmed) {
        alert('Nama tidak boleh kosong');
        return;
    }
    if (type === 'kategori') {
        submitHiddenForm('/setting/kategori/update', { id_kategori: id, nama_kategori: trimmed });
    } else {
        submitHiddenForm('/setting/satuan/update', { id_satuan: id, nama_satuan: trimmed });
    }
}

function deleteItem(type, id) {
    if (!confirm('Yakin ingin menghapus ' + type + ' ini?')) return;
    if (type === 'kategori') {
        submitHiddenForm('/setting/kategori/delete', { id_kategori: id });
    } else {
        submitHiddenForm('/setting/satuan/delete', { id_satuan: id });
    }
}
</script>

<?php 
$content = ob_get_clean();
$title = 'Kategori & Satuan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
