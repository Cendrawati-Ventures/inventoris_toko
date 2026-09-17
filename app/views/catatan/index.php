<?php ob_start(); ?>
<div class="app-card p-4 sm:p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800"><i class="fas fa-clipboard-list mr-2 text-teal-600" aria-hidden="true"></i>Catatan Operasional</h2>
        <p class="mt-2 text-sm text-slate-500"><?= $isAdmin ? 'Catatan kegiatan dan kendala operasional dari kasir.' : 'Catat kegiatan atau kendala operasional.' ?></p>
    </div>
    <?php if (!$isAdmin): ?>
        <form action="/catatan-operasional/store" method="POST" class="rounded-2xl border border-teal-200 bg-teal-50/50 p-4 mb-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['catatan_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-4">
                <label for="tanggal_catatan" class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Catatan *</label>
                <input type="date" id="tanggal_catatan" name="tanggal" required value="<?= htmlspecialchars($dateDraft ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <label for="catatan_operasional" class="block text-sm font-semibold text-slate-700 mb-2">Tambah Catatan Operasional</label>
            <textarea id="catatan_operasional" name="catatan" rows="5" required maxlength="4000" placeholder="Contoh: Kertas struk habis, perlu dibeli untuk shift berikutnya." class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"><?= htmlspecialchars($draft, ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="mt-4">
                <label for="uang_dikeluarkan" class="block text-sm font-semibold text-slate-700 mb-2">Uang Dikeluarkan</label>
                <div class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden focus-within:ring-2 focus-within:ring-teal-500">
                    <span class="px-3 text-slate-500" aria-hidden="true">Rp</span>
                    <input type="text" id="uang_dikeluarkan" name="uang_dikeluarkan" inputmode="decimal" autocomplete="off" maxlength="30" value="<?= htmlspecialchars($expenseDraft ?? '0', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 60.000" aria-describedby="uang_hint" class="w-full py-2 pr-3 outline-none bg-transparent">
                </div>
                <p id="uang_hint" class="mt-1 text-xs text-slate-500">Isi 0 jika tidak ada pengeluaran.</p>
            </div>
            <div class="mt-4">
                <label for="uang_di_kasir" class="block text-sm font-semibold text-slate-700 mb-2">Uang Tersisa di Kasir *</label>
                <div class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden focus-within:ring-2 focus-within:ring-teal-500">
                    <span class="px-3 text-slate-500" aria-hidden="true">Rp</span>
                    <input type="text" id="uang_di_kasir" name="uang_di_kasir" required inputmode="decimal" autocomplete="off" maxlength="30" value="<?= htmlspecialchars($cashDraft ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 500.000" aria-describedby="kas_hint" class="w-full py-2 pr-3 outline-none bg-transparent">
                </div>
                <p id="kas_hint" class="mt-1 text-xs text-slate-500">Uang tunai yang tersisa setelah pengeluaran, sebagai acuan kembalian hari berikutnya. Isi 0 jika kosong.</p>
            </div>
            <div class="mt-3 flex flex-wrap gap-3 items-center justify-between">
                <span class="text-xs text-slate-500">Maksimal 4.000 huruf.</span>
                <button type="submit" class="app-btn-primary px-4 py-2 font-semibold"><i class="fas fa-save mr-2" aria-hidden="true"></i>Simpan Catatan</button>
            </div>
        </form>
    <?php endif; ?>
    <div class="flex items-center justify-between gap-3 mb-4">
        <h3 class="font-bold text-slate-700"><?= $isAdmin ? 'Catatan dari Kasir' : 'Catatan Saya' ?></h3>
        <span class="text-xs rounded-full bg-slate-100 px-3 py-1 whitespace-nowrap"><?= $total ?> catatan</span>
    </div>
    <div class="space-y-3">
        <?php if (!$notes): ?>
            <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-slate-500">Belum ada catatan operasional.</p>
        <?php endif; ?>
        <?php foreach ($notes as $note): ?>
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap justify-between gap-2 mb-3 text-sm">
                    <span class="font-semibold text-teal-700"><?= htmlspecialchars($note['nama_kasir'], ENT_QUOTES, 'UTF-8') ?></span>
                    <time class="text-xs text-slate-500" datetime="<?= htmlspecialchars($note['tanggal'] ?? substr($note['created_at'], 0, 10), ENT_QUOTES, 'UTF-8') ?>">Tanggal: <?= formatTanggal($note['tanggal'] ?? $note['created_at']) ?></time>
                </div>
                <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 flex flex-wrap justify-between gap-2">
                    <span class="text-sm text-amber-800">Uang Dikeluarkan</span>
                    <span class="font-bold text-amber-900">Rp <?= number_format((float)($note['uang_dikeluarkan'] ?? 0), fmod((float)($note['uang_dikeluarkan'] ?? 0), 1.0) == 0.0 ? 0 : 2, ',', '.') ?></span>
                </div>
                <div class="mb-3 rounded-lg border border-teal-200 bg-teal-50 px-3 py-2">
                    <div class="flex flex-wrap justify-between gap-2">
                        <span class="text-sm text-teal-800">Uang Tersisa di Kasir</span>
                        <span class="font-bold text-teal-900"><?php if (($note['uang_di_kasir'] ?? null) === null): ?>Belum dicatat<?php else: ?>Rp <?= number_format((float)$note['uang_di_kasir'], fmod((float)$note['uang_di_kasir'], 1.0) == 0.0 ? 0 : 2, ',', '.') ?><?php endif; ?></span>
                    </div>
                    <p class="mt-1 text-xs text-teal-700">Saldo tunai saat pencatatan · acuan kembalian hari berikutnya</p>
                </div>
                <p class="whitespace-pre-wrap break-words text-sm text-slate-700 leading-relaxed"><?= htmlspecialchars($note['catatan'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
        <div class="flex flex-wrap items-center justify-between gap-3 mt-5 text-sm">
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?><a class="app-btn-secondary px-3 py-2" href="/catatan-operasional?page=<?= $page - 1 ?>">Sebelumnya</a><?php endif; ?>
                <?php if ($page < $pages): ?><a class="app-btn-secondary px-3 py-2" href="/catatan-operasional?page=<?= $page + 1 ?>">Berikutnya</a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php if (!$isAdmin): ?>
<script src="/assets/js/money.js"></script>
<script src="/assets/js/catatan-money.js"></script>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Catatan Operasional - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
