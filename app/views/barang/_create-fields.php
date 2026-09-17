        <div class="mb-6">
            <label for="kode_barang" class="block text-gray-700 font-bold mb-2 text-sm">Kode Barang *</label>
            <input type="text" id="kode_barang" name="kode_barang" required
                   placeholder="Misal: BRG-001"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="nama_barang" class="block text-gray-700 font-bold mb-2 text-sm">Nama Barang *</label>
            <input type="text" id="nama_barang" name="nama_barang" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="kategori" class="block text-gray-700 font-bold mb-2 text-sm">Kategori *</label>
            <select id="kategori" name="id_kategori" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori as $kat): ?>
                    <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-blue-50 p-4 sm:p-5 shadow-[0_12px_30px_rgba(15,23,42,0.04)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <label class="block text-slate-800 font-bold text-sm">Satuan & Harga</label>
                    <p class="mt-1 text-xs text-slate-500">Isi = jumlah unit dasar per kemasan. Gunakan nilai 1 untuk satuan dasar; harga mengikuti masing-masing satuan.</p>
                </div>
                <button type="button" id="btnAddSatuanDetail" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-3 py-2 text-sm font-semibold shadow-sm transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i>Tambah Satuan
                </button>
            </div>

            <div class="mb-3 grid grid-cols-6 gap-2 px-2 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 md:grid-cols-[1.15fr_0.8fr_1.2fr_1.2fr_0.9fr_0.45fr]">
                <span class="truncate">Satuan</span>
                <span class="truncate">Isi</span>
                <span class="truncate">Harga Beli</span>
                <span class="truncate">Harga Jual</span>
                <span class="truncate">Keuntungan</span>
                <span class="text-center truncate">Hapus</span>
            </div>

            <div id="satuan_detail_container" class="space-y-3"></div>
            <input type="hidden" name="satuan_detail" id="satuan_detail_input" value="[]">
            <input type="hidden" name="satuan" id="default_satuan" value="">
            <input type="hidden" name="harga_beli" id="default_harga_beli" value="0">
            <input type="hidden" name="harga_jual" id="default_harga_jual" value="0">
        </div>

        <p id="price_notice" class="hidden -mt-1 mb-2 text-sm text-red-600 font-semibold">
            Harga jual harus selalu lebih tinggi dari harga beli.
        </p>

        <div class="mb-8">
            <label for="stok" class="block text-gray-700 font-bold mb-2 text-sm">Stok Awal (satuan dasar)</label>
            <input type="number" id="stok" name="stok" required min="0" value="0"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-8">
            <label for="tanggal_expired" class="block text-gray-700 font-bold mb-2 text-sm">Tanggal Expired</label>
            <input type="date" id="tanggal_expired" name="tanggal_expired"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika barang tidak memiliki tanggal kedaluwarsa.</p>
        </div>
