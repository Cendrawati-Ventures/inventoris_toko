<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Detail Penjualan
    </h2>

    <!-- Info Transaksi -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b-2 border-gray-400">Informasi Transaksi</h3>
            <div class="space-y-5">
                <div class="flex justify-between items-start">
                    <span class="text-gray-600 text-sm font-medium">Tanggal:</span>
                    <span class="text-gray-800 font-bold text-right"><?= date('d/m/Y', strtotime($penjualan['tanggal'])) ?></span>
                </div>
                <div class="flex justify-between items-start border-t pt-4">
                    <span class="text-gray-600 text-sm font-medium">Waktu:</span>
                    <span class="text-gray-800 font-bold text-right"><?= date('H:i', strtotime($penjualan['tanggal'])) ?></span>
                </div>
                <div class="flex justify-between items-start border-t pt-4">
                    <span class="text-gray-600 text-sm font-medium">Nama Pembeli:</span>
                    <span class="text-gray-800 font-bold text-right"><?= htmlspecialchars($penjualan['nama_pembeli'] ?? '-') ?></span>
                </div>
                <?php if (!empty($penjualan['keterangan'])): ?>
                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm font-medium mb-2">Keterangan:</p>
                    <p class="text-gray-800 bg-white p-3 rounded border border-gray-200 text-sm"><?= htmlspecialchars($penjualan['keterangan']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-blue-900 mb-6 pb-3 border-b-2 border-blue-400">Ringkasan Pembayaran</h3>
            <div class="space-y-5">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 text-sm font-medium">Total Harga:</span>
                    <span class="text-2xl font-bold text-blue-600"><?= formatRupiah($penjualan['total_harga']) ?></span>
                </div>
                <div class="flex justify-between items-center border-t-2 border-blue-200 pt-4">
                    <span class="text-gray-700 text-sm font-medium">Uang Diberikan:</span>
                    <span class="text-xl font-bold text-gray-800"><?= formatRupiah($penjualan['uang_diberikan']) ?></span>
                </div>
                <div class="flex justify-between items-center border-t-2 border-blue-200 pt-4">
                    <span class="text-gray-700 text-sm font-medium">Kembalian:</span>
                    <span class="text-2xl font-bold <?= $penjualan['kembalian'] >= 0 ? 'text-green-600' : 'text-red-600' ?>"><?= formatRupiah($penjualan['kembalian']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Item -->
    <div class="mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b-2 border-gray-300">Item Penjualan</h3>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 rounded-lg">
                <thead class="bg-blue-100 border-b-2 border-blue-300">
                    <tr>
                        <th class="px-6 py-4 text-center text-sm font-bold w-12">No</th>
                        <th class="px-6 py-4 text-left text-sm font-bold w-20">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-bold w-40">Nama Barang</th>
                        <th class="px-6 py-4 text-center text-sm font-bold w-28">Jumlah</th>
                        <th class="px-6 py-4 text-right text-sm font-bold w-32">Harga Jual</th>
                        <th class="px-6 py-4 text-right text-sm font-bold w-28">Diskon</th>
                        <th class="px-6 py-4 text-right text-sm font-bold w-32">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($details as $index => $detail): ?>
                    <tr class="hover:bg-blue-50 transition duration-200">
                        <td class="px-6 py-4 text-center font-medium text-gray-800"><?= $index + 1 ?></td>
                        <td class="px-6 py-4 font-mono text-sm text-gray-600"><?= htmlspecialchars($detail['kode_barang'] ?? '-') ?></td>
                        <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($detail['nama_barang']) ?></td>
                        <td class="px-6 py-4 text-center font-medium text-gray-800"><?= $detail['jumlah'] ?> <?= $detail['satuan'] ?></td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800"><?= formatRupiah($detail['harga_satuan']) ?></td>
                        <td class="px-6 py-4 text-right font-semibold <?= $detail['diskon'] > 0 ? 'text-red-600' : 'text-gray-500' ?>"><?= $detail['diskon'] > 0 ? formatRupiah($detail['diskon']) : '-' ?></td>
                        <td class="px-6 py-4 text-right font-bold text-green-600"><?= formatRupiah($detail['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Buttons -->
    <div class="flex gap-4 justify-center">
        <a href="/penjualan/edit/<?= $penjualan['id_penjualan'] ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-edit mr-2"></i>Edit
        </a>
        <a href="/penjualan/delete/<?= $penjualan['id_penjualan'] ?>" 
           onclick="return confirm('Yakin ingin menghapus penjualan ini? Stok akan dikembalikan.')"
           class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-trash mr-2"></i>Hapus
        </a>
        <a href="/penjualan" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = 'Detail Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
