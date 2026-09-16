<?php ob_start(); ?>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 max-w-4xl mx-auto">
    <div class="mb-8 flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Produk</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-800">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Tambah Barang Baru
            </h2>
        </div>
        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 border border-blue-100">
            Form Baru
        </span>
    </div>

    <form action="/barang/store" method="POST" id="formBarangCreate" class="space-y-6">
        <?php include __DIR__ . '/_create-fields.php'; ?>

        <div class="flex gap-4 justify-center">
            <button type="submit" class="app-btn-primary px-8 py-3 font-semibold" data-loading-text="Menyimpan...">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
            <a href="/barang" class="app-btn-secondary px-8 py-3 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<script src="/assets/js/money.js"></script>
<?php $barangFormId = 'formBarangCreate'; include __DIR__ . '/_create-script.php'; ?>

<?php 
$content = ob_get_clean();
$title = 'Tambah Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
