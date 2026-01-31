<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">
        <i class="fas fa-receipt text-blue-600 mr-2"></i>Konfigurasi Format Nota
    </h2>
    <p class="text-gray-600 mb-6">Atur tampilan dan format nota yang akan dicetak untuk setiap transaksi penjualan</p>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
            <i class="fas fa-check-circle"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="/setting/nota" method="POST">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- LEFT COLUMN: Form Input -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Informasi Toko -->
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 rounded-lg p-4">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-store"></i>Informasi Toko
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="nama_toko" class="block text-gray-700 font-semibold mb-2">
                                Nama Toko <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="nama_toko" name="nama_toko" 
                                   value="<?= htmlspecialchars($config['nama_toko'] ?? 'UD. BERSAUDARA') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   required>
                            <small class="text-gray-500">Nama yang ditampilkan di bagian atas nota</small>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="nomor_telepon" class="block text-gray-700 font-semibold mb-2">
                                    <i class="fas fa-phone text-gray-600 mr-2"></i>Nomor Telepon
                                </label>
                                <input type="text" id="nomor_telepon" name="nomor_telepon" 
                                       value="<?= htmlspecialchars($config['nomor_telepon'] ?? '') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="email_toko" class="block text-gray-700 font-semibold mb-2">
                                    <i class="fas fa-envelope text-gray-600 mr-2"></i>Email Toko
                                </label>
                                <input type="email" id="email_toko" name="email_toko" 
                                       value="<?= htmlspecialchars($config['email_toko'] ?? '') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="alamat_toko" class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-map-marker-alt text-gray-600 mr-2"></i>Alamat Toko
                            </label>
                            <textarea id="alamat_toko" name="alamat_toko" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($config['alamat_toko'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Format Kertas & Font -->
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border-l-4 border-yellow-500 rounded-lg p-4">
                    <h3 class="text-lg font-bold text-yellow-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-sliders-h"></i>Format Nota
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="lebar_kertas" class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-ruler-horizontal text-gray-600 mr-2"></i>Lebar Kertas <span class="text-red-600">*</span>
                            </label>
                            <select id="lebar_kertas" name="lebar_kertas" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="40" <?= ($config['lebar_kertas'] ?? 80) == 40 ? 'selected' : '' ?>>40 mm (Label Printer)</option>
                                <option value="58" <?= ($config['lebar_kertas'] ?? 80) == 58 ? 'selected' : '' ?>>58 mm (Thermal Printer Standard)</option>
                                <option value="76" <?= ($config['lebar_kertas'] ?? 80) == 76 ? 'selected' : '' ?>>76 mm (Thermal Printer)</option>
                                <option value="80" <?= ($config['lebar_kertas'] ?? 80) == 80 ? 'selected' : '' ?>>80 mm (Thermal Printer Wide)</option>
                                <option value="100" <?= ($config['lebar_kertas'] ?? 80) == 100 ? 'selected' : '' ?>>100 mm (Kertas Lebar)</option>
                                <option value="105" <?= ($config['lebar_kertas'] ?? 80) == 105 ? 'selected' : '' ?>>105 mm (A6)</option>
                                <option value="148" <?= ($config['lebar_kertas'] ?? 80) == 148 ? 'selected' : '' ?>>148 mm (A5)</option>
                                <option value="210" <?= ($config['lebar_kertas'] ?? 80) == 210 ? 'selected' : '' ?>>210 mm (A4)</option>
                            </select>
                            <small class="text-gray-500">Sesuaikan dengan ukuran printer Anda</small>
                        </div>
                        <div>
                            <label for="font_nota" class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-font text-gray-600 mr-2"></i>Jenis Font <span class="text-red-600">*</span>
                            </label>
                            <select id="font_nota" name="font_nota" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Arial" <?= ($config['font_nota'] ?? 'Arial') == 'Arial' ? 'selected' : '' ?>>Arial</option>
                                <option value="Times New Roman" <?= ($config['font_nota'] ?? 'Arial') == 'Times New Roman' ? 'selected' : '' ?>>Times New Roman</option>
                                <option value="Calibri" <?= ($config['font_nota'] ?? 'Arial') == 'Calibri' ? 'selected' : '' ?>>Calibri</option>
                                <option value="Georgia" <?= ($config['font_nota'] ?? 'Arial') == 'Georgia' ? 'selected' : '' ?>>Georgia</option>
                                <option value="Courier New" <?= ($config['font_nota'] ?? 'Arial') == 'Courier New' ? 'selected' : '' ?>>Courier New</option>
                                <option value="Verdana" <?= ($config['font_nota'] ?? 'Arial') == 'Verdana' ? 'selected' : '' ?>>Verdana</option>
                                <option value="Tahoma" <?= ($config['font_nota'] ?? 'Arial') == 'Tahoma' ? 'selected' : '' ?>>Tahoma</option>
                            </select>
                            <small class="text-gray-500">Font akan ditampilkan di preview</small>
                        </div>
                    </div>
                </div>

                <!-- Konten Tambahan -->
                <div class="bg-gradient-to-r from-purple-50 to-purple-100 border-l-4 border-purple-500 rounded-lg p-4">
                    <h3 class="text-lg font-bold text-purple-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-align-center"></i>Konten Tambahan
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="custom_header_text" class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-arrow-down text-gray-600 mr-2"></i>Header Tambahan
                            </label>
                            <textarea id="custom_header_text" name="custom_header_text" rows="3" 
                                      placeholder="Contoh: Selamat Berbelanja di toko kami!"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($config['custom_header_text'] ?? '') ?></textarea>
                            <small class="text-gray-500">Teks yang ditampilkan setelah header toko</small>
                        </div>
                        <div>
                            <label for="footer_nota" class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-arrow-up text-gray-600 mr-2"></i>Footer Nota
                            </label>
                            <textarea id="footer_nota" name="footer_nota" rows="3" maxlength="200"
                                      placeholder="Contoh: Terima kasih atas pembelian Anda!"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($config['footer_nota'] ?? '') ?></textarea>
                            <small class="text-gray-500">Maks 200 karakter</small>
                        </div>
                    </div>
                </div>

                <!-- Opsi Tampilan -->
                <div class="bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 rounded-lg p-4">
                    <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-eye"></i>Opsi Tampilan
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="tampilkan_jam" <?= ($config['tampilkan_jam'] ?? 1) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-clock text-gray-600 mr-2"></i>Tampilkan Jam
                            </span>
                        </label>
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="tampilkan_kode_barang" <?= ($config['tampilkan_kode_barang'] ?? 1) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-barcode text-gray-600 mr-2"></i>Kode Barang
                            </span>
                        </label>
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="tampilkan_satuan" <?= ($config['tampilkan_satuan'] ?? 1) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-cubes text-gray-600 mr-2"></i>Satuan Barang
                            </span>
                        </label>
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="tampilkan_nama_pembeli" <?= ($config['tampilkan_nama_pembeli'] ?? 1) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-user text-gray-600 mr-2"></i>Nama Pembeli
                            </span>
                        </label>
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="jumlah_diskon_terpisah" <?= ($config['jumlah_diskon_terpisah'] ?? 0) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-tag text-gray-600 mr-2"></i>Diskon Terpisah
                            </span>
                        </label>
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="tampilkan_info_hutang" <?= ($config['tampilkan_info_hutang'] ?? 1) ? 'checked' : '' ?>
                                   class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                            <span class="ml-3 text-gray-700 font-medium">
                                <i class="fas fa-info-circle text-gray-600 mr-2"></i>Info Hutang
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                        <i class="fas fa-save text-lg"></i>Simpan Konfigurasi
                    </button>
                    <a href="/setting/kategori-satuan" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left text-lg"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- RIGHT COLUMN: Preview -->
            <div class="lg:col-span-1">
                <div class="sticky top-6 bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-eye text-blue-600"></i>Preview Nota
                    </h3>
                    <div style="font-family:<?= htmlspecialchars($config['font_nota'] ?? 'Arial') ?>, monospace;
                                width:<?= ($config['lebar_kertas'] ?? 80) ?>mm;
                                background:white;
                                border:2px solid #333;
                                padding:5mm;
                                margin:0 auto;
                                font-size:9px;
                                line-height:1.3;
                                box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                        <div style="text-align:center;margin-bottom:3mm;border-bottom:2px solid #000;padding-bottom:3mm;">
                            <strong style="font-size:11px;"><?= htmlspecialchars($config['nama_toko'] ?? 'UD. BERSAUDARA') ?></strong>
                            <?php if ($config['alamat_toko']): ?>
                                <div style="font-size:8px;color:#666;margin-top:1mm;"><?= htmlspecialchars($config['alamat_toko']) ?></div>
                            <?php endif; ?>
                            <?php if ($config['nomor_telepon']): ?>
                                <div style="font-size:8px;color:#666;">Tlp: <?= htmlspecialchars($config['nomor_telepon']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="text-align:center;font-size:8px;margin-bottom:2mm;">
                            Tanggal: 27/01/2026 <?php if ($config['tampilkan_jam'] ?? 1): ?>14:30<?php endif; ?>
                        </div>
                        <table style="width:100%;font-size:8px;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:1px solid #000;">
                                    <th style="text-align:left;">Item</th>
                                    <th style="text-align:right;">Qty</th>
                                    <th style="text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding:2px 0;">Barang Sample</td>
                                    <td style="text-align:right;padding:2px 0;">2</td>
                                    <td style="text-align:right;padding:2px 0;">20.000</td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="text-align:right;margin-top:2mm;padding-top:2mm;border-top:1px solid #000;font-weight:bold;">
                            <div>Total: 20.000</div>
                        </div>
                        <?php if ($config['footer_nota']): ?>
                            <div style="text-align:center;font-size:8px;margin-top:3mm;color:#666;border-top:1px solid #000;padding-top:2mm;">
                                <?= htmlspecialchars($config['footer_nota']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <small class="text-gray-500 block text-center mt-4">Update preview dengan mengubah konfigurasi</small>
                </div>
            </div>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean();
$title = 'Konfigurasi Format Nota - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
