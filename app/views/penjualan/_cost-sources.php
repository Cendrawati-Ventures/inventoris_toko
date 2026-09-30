<section class="no-print rounded-2xl border border-teal-200 bg-teal-50 p-4 mb-4">
    <h3 class="font-bold text-slate-800">Sesuaikan Modal Satuan</h3>
    <p class="text-sm text-slate-600 mt-1">Gunakan harga beli satuan dari Stok Barang saat ini untuk menghitung ulang laba transaksi ini. Stok, harga jual, diskon, dan pembayaran tetap.</p>
    <?php if (!empty($costPreviewError)): ?>
        <p class="mt-3 text-sm text-amber-800"><?= htmlspecialchars($costPreviewError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif (!empty($costPreview['rows'])): ?>
        <div class="overflow-x-auto mt-3">
            <table class="w-full text-sm text-left">
                <thead><tr><th class="p-2">Barang / Satuan</th><th class="p-2">Modal Lama</th><th class="p-2">Modal Baru</th><th class="p-2">Laba Baru</th></tr></thead>
                <tbody><?php foreach ($costPreview['rows'] as $row): ?>
                    <tr class="border-t border-teal-100">
                        <td class="p-2"><?= htmlspecialchars($row['nama_barang'] . ' / ' . $row['satuan'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="p-2 whitespace-nowrap"><?= formatRupiah($row['modal_lama']) ?></td>
                        <td class="p-2 whitespace-nowrap"><?= formatRupiah($row['modal_baru']) ?></td>
                        <td class="p-2 whitespace-nowrap"><?= formatRupiah($row['laba_baru']) ?></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table>
        </div>
        <form action="/penjualan/koreksi-modal/<?= (int)$penjualan['id_penjualan'] ?>" method="POST" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['unit_cost_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="signature" value="<?= htmlspecialchars($costPreview['signature'], ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="app-btn-primary px-4 py-2">Terapkan Koreksi Modal</button>
        </form>
    <?php endif; ?>
</section>
<details class="no-print rounded-2xl border border-slate-200 bg-slate-50 p-4 mb-6">
    <summary class="cursor-pointer font-bold text-slate-800">Periksa Sumber Modal</summary>
    <p class="text-sm text-slate-600 mt-2">Laba menggunakan modal satuan yang tersimpan pada transaksi. Rincian batch di bawah mencatat asal stok dan biaya pembeliannya; nilainya dapat berbeda dari harga beli satuan.</p>
    <?php foreach ($details as $item): ?>
        <section class="mt-4 rounded-xl border border-slate-200 bg-white p-3">
            <h4 class="font-semibold"><?= htmlspecialchars($item['nama_barang'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($item['kode_barang'], ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-sm mt-1">Modal tersimpan: <?= formatRupiah($item['harga_beli_item']) ?> / <?= htmlspecialchars($item['satuan'], ENT_QUOTES, 'UTF-8') ?> · Isi saat penjualan: <?= htmlspecialchars((string)($item['nilai_satuan'] ?? 1), ENT_QUOTES, 'UTF-8') ?> unit dasar</p>
            <?php $sources = $costSources[$item['id_detail']] ?? []; ?>
            <?php if (!$sources): ?>
                <p class="text-sm text-amber-800 mt-2">Rincian sumber modal transaksi ini belum tercatat.</p>
            <?php endif; ?>
            <?php foreach ($sources as $source): ?>
                <div class="border-t border-slate-100 mt-3 pt-3 text-sm space-y-1">
                    <p class="font-medium"><?= $source['id_batch'] !== null ? 'Batch #' . (int)$source['id_batch'] : 'Modal cadangan tanpa batch barang masuk' ?></p>
                    <p><?= htmlspecialchars((string)$source['qty'], ENT_QUOTES, 'UTF-8') ?> unit dasar × Rp <?= number_format((float)$source['harga_modal'], 2, ',', '.') ?> = Rp <?= number_format((float)$source['total_modal'], 2, ',', '.') ?></p>
                    <?php if ($source['id_pembelian'] !== null): ?>
                        <p>Barang masuk #<?= (int)$source['id_pembelian'] ?> · <?= htmlspecialchars((string)$source['tanggal_batch'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p>Harga beli: <?= formatRupiah($source['harga_beli']) ?> / <?= htmlspecialchars((string)$source['satuan_beli'], ENT_QUOTES, 'UTF-8') ?> · Isi saat pembelian: <?= htmlspecialchars((string)$source['isi_beli'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</details>
