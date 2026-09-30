<details class="no-print rounded-2xl border border-slate-200 bg-slate-50 p-4 mb-6">
    <summary class="cursor-pointer font-bold text-slate-800">Periksa Sumber Modal</summary>
    <p class="text-sm text-slate-600 mt-2">Modal tersimpan saat penjualan. Mengedit harga barang tidak mengubah modal transaksi lama.</p>
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
