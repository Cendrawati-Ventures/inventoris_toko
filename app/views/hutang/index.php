<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-file-invoice-dollar text-orange-600 mr-2"></i>Daftar Hutang
    </h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Belum Lunas</p>
                    <p class="text-2xl font-bold text-red-600">
                        <?= formatRupiah($summary['total_belum_lunas'] ?? 0) ?>
                    </p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Lunas</p>
                    <p class="text-2xl font-bold text-green-600">
                        <?= formatRupiah($summary['total_lunas'] ?? 0) ?>
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Semua</p>
                    <p class="text-2xl font-bold text-blue-600">
                        <?= formatRupiah($summary['total_semua'] ?? 0) ?>
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-coins text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-2">
            <a href="/hutang?filter=semua" 
               class="px-4 py-2 rounded-lg transition <?= (!isset($_GET['filter']) || $_GET['filter'] == 'semua') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                <i class="fas fa-list mr-2"></i>Semua
            </a>
            <a href="/hutang?filter=belum_bayar" 
               class="px-4 py-2 rounded-lg transition <?= (isset($_GET['filter']) && $_GET['filter'] == 'belum_bayar') ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                <i class="fas fa-exclamation-circle mr-2"></i>Belum Bayar
            </a>
            <a href="/hutang?filter=lunas" 
               class="px-4 py-2 rounded-lg transition <?= (isset($_GET['filter']) && $_GET['filter'] == 'lunas') ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                <i class="fas fa-check-circle mr-2"></i>Lunas
            </a>
            <a href="/hutang?filter=jatuh_tempo" 
               class="px-4 py-2 rounded-lg transition <?= (isset($_GET['filter']) && $_GET['filter'] == 'jatuh_tempo') ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
                <i class="fas fa-calendar-times mr-2"></i>Sudah Jatuh Tempo
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase">No</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase">Nama Penghutang</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase">Jumlah Hutang</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase">Tanggal Transaksi</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase">Jatuh Tempo</th>
                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="py-3 px-4 text-center text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($hutang)): ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data hutang</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($hutang as $index => $item): ?>
                        <?php
                        $isJatuhTempo = (strtotime($item['jatuh_tempo']) < time()) && $item['status'] != 'lunas';
                        $statusClass = [
                            'belum_bayar' => 'bg-red-100 text-red-800',
                            'lunas' => 'bg-green-100 text-green-800'
                        ];
                        $statusText = [
                            'belum_bayar' => 'Belum Bayar',
                            'lunas' => 'Lunas'
                        ];
                        ?>
                        <tr class="hover:bg-gray-50 <?= $isJatuhTempo ? 'bg-orange-50' : '' ?>">
                            <td class="py-3 px-4"><?= $index + 1 ?></td>
                            <td class="py-3 px-4">
                                <div class="font-semibold"><?= htmlspecialchars($item['nama_penghutang']) ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-blue-600"><?= formatRupiah($item['jumlah_hutang']) ?></div>
                                <div class="text-xs text-gray-500">Total: <?= formatRupiah($item['total_harga']) ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <?= date('d/m/Y H:i', strtotime($item['tanggal_transaksi'])) ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="<?= $isJatuhTempo ? 'text-red-600 font-semibold' : '' ?>">
                                    <?= date('d/m/Y', strtotime($item['jatuh_tempo'])) ?>
                                </div>
                                <?php if ($isJatuhTempo): ?>
                                    <div class="text-xs text-red-600">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Sudah Jatuh Tempo
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusClass[$item['status']] ?>">
                                    <?= $statusText[$item['status']] ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center gap-2">
                                    <?php if ($item['status'] != 'lunas'): ?>
                                        <div class="relative group">
                                            <button type="button" onclick="showStatusModal(<?= $item['id_hutang'] ?>, '<?= $item['status'] ?>')" 
                                                    class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded transition">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                                Update Status
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="relative group">
                                        <a href="/penjualan/detail/<?= $item['id_penjualan'] ?>" 
                                           class="bg-green-500 hover:bg-green-600 text-white p-2 rounded transition inline-block">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                            Lihat Transaksi
                                        </span>
                                    </div>
                                    <div class="relative group">
                                        <button type="button" onclick="confirmDelete(<?= $item['id_hutang'] ?>)" 
                                                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                            Hapus
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Update Status -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Update Status Hutang</h3>
        <form id="statusForm" method="POST">
            <input type="hidden" id="hutang_id" name="hutang_id">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Status Baru</label>
                <select name="status" id="status_select" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="belum_bayar">Belum Bayar</option>
                    <option value="lunas">Lunas</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
                <button type="button" onclick="closeStatusModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Konfirmasi Hapus</h3>
        <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus data hutang ini?</p>
        <form id="deleteForm" method="POST">
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-trash mr-2"></i>Hapus
                </button>
                <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showStatusModal(id, currentStatus) {
    document.getElementById('hutang_id').value = id;
    document.getElementById('status_select').value = currentStatus;
    document.getElementById('statusForm').action = '/hutang/update-status/' + id;
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

function confirmDelete(id) {
    document.getElementById('deleteForm').action = '/hutang/delete/' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php 
$content = ob_get_clean();
$title = 'Daftar Hutang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
