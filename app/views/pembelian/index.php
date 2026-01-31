<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>Daftar Pembelian
        </h2>
        <a href="/pembelian/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
            <i class="fas fa-plus mr-2"></i>Tambah Pembelian
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg">
            <thead class="bg-blue-100 border-b-2 border-blue-300">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-12">No</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-32">Tanggal</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-40">Supplier</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-48">Item (Jumlah)</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Total</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($pembelian)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data pembelian</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pembelian as $index => $item): ?>
                        <tr class="hover:bg-blue-50 transition duration-200 border-b border-gray-200">
                            <td class="px-6 py-4 text-sm text-gray-700 font-medium text-center"><?= $index + 1 ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-semibold"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></span>
                                    <span class="text-gray-500 text-xs"><?= date('H:i', strtotime($item['tanggal'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_pembeli'] ?: '-') ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="space-y-1">
                                    <p class="text-gray-800 font-medium"><?= htmlspecialchars($item['barang_list']) ?></p>
                                    <p class="text-gray-500 text-xs bg-gray-100 inline-block px-2 py-1 rounded">
                                        <i class="fas fa-box mr-1"></i><?= $item['jumlah_item'] ?> item(s)
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-lg text-green-600">
                                    Rp <?= number_format($item['total_harga'], 0, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="/pembelian/detail/<?= $item['id_pembelian'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white rounded transition" title="Lihat">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                    <a href="/pembelian/edit/<?= $item['id_pembelian'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-600 hover:bg-yellow-600 hover:text-white rounded transition" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form action="/pembelian/delete/<?= $item['id_pembelian'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pembelian ini?');">
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded transition" title="Hapus">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = 'Daftar Pembelian - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
