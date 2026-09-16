// Run with: node --test tests/barang-picker.test.cjs
const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

for (const view of ['penjualan/create.php', 'penjualan/edit.php', 'pembelian/create.php']) {
    test(`${view}: all products, search, special characters and unit selection`, () => {
        const products = Array.from({ length: 150 }, (_, i) => ({
            id_barang: i + 1, nama_barang: `Barang ${i + 1}`, kode_barang: i + 1,
            stok: i === 149 ? 0 : 100, satuan: 'pcs', harga_jual: 2000, harga_beli: 1000,
            satuan_detail: [
                { satuan: 'pcs', nilai: 1, harga_jual: 2000, harga_beli: 1000 },
                { satuan: 'dus', nilai: 12, harga_jual: 24000, harga_beli: 12000 }
            ]
        }));
        products[0].nama_barang = 'Sabun &quot;A&quot; "B" O\'Brien <contoh>';
        const elements = {
            search_barang_main: { addEventListener() {} },
            barang_list: { innerHTML: '' }, barang_count_info: {},
            selected_container: {
                html: '', querySelectorAll: () => [],
                insertAdjacentHTML(position, html) { this.html = html; }
            },
            no_items_msg: { style: {} }
        };
        const source = fs.readFileSync(path.join(__dirname, '../app/views', view), 'utf8');
        let script = source.match(/<script>([\s\S]*?)<\/script>/)[1];
        script = script.replace(/<\?=\s*json_encode\(array_values\(\$barang[^]*?\?>/, JSON.stringify(products));
        script = script.replace(/<\?=[\s\S]*?\?>/g, '[]');
        const context = vm.createContext({
            console: { log() {} }, setTimeout() {},
            document: {
                readyState: 'loading', addEventListener() {},
                getElementById: id => elements[id] || null,
                querySelectorAll: () => []
            },
            window: { addEventListener() {} }
        });
        vm.runInContext(fs.readFileSync(path.join(__dirname, '../public/assets/js/stock-units.js'), 'utf8'), context);
        vm.runInContext(fs.readFileSync(path.join(__dirname, '../public/assets/js/money.js'), 'utf8'), context);
        vm.runInContext(script, context);
        context.renderBarangList();
        assert.equal((elements.barang_list.innerHTML.match(/data-barang-card=/g) || []).length, 150);
        assert.equal(elements.barang_count_info.textContent, '150 barang');
        assert.ok(elements.barang_list.innerHTML.includes('Sabun &amp;quot;A&amp;quot;'));
        assert.ok(!elements.barang_list.innerHTML.includes('data-barang-json'));
        context.renderBarangList('150');
        assert.equal(elements.barang_count_info.textContent, '1 barang');
        assert.ok(elements.barang_list.innerHTML.includes('Barang 150'));
        if (view.startsWith('penjualan')) assert.ok(elements.barang_list.innerHTML.includes('Stok Habis'));
        context.renderBarangList('tidak ditemukan');
        assert.equal(elements.barang_count_info.textContent, '0 barang');
        context.renderBarangList('');
        assert.equal(elements.barang_count_info.textContent, '150 barang');

        // Exercise the real button handler and row creation, without a database.
        context.hitungTotal = () => {};
        context.updateSelectedCount = () => {};
        context.showToast = () => {};
        const button = {
            getAttribute: name => name === 'data-fallback-unit' ? 'pcs' : '1',
            closest: () => ({ querySelector: () => ({ value: 'dus' }) })
        };
        context.addItemFromBarangButton(button);
        assert.ok(elements.selected_container.html.includes('Sabun &amp;quot;A&amp;quot;'));
        assert.ok(elements.selected_container.html.includes('value="dus"'));
        assert.ok(elements.selected_container.html.includes(view.startsWith('penjualan') ? '24000' : '12.000'));
        assert.equal(elements.no_items_msg.style.display, 'none');
        if (view.startsWith('penjualan')) assert.ok(elements.selected_container.html.includes('data-nilai-satuan="12"'));
        if (view === 'pembelian/create.php') {
            const card = context.createBarangCard(products[0]);
            assert.ok(!card.includes('<select'));
            assert.ok(!card.includes('Harga Beli'));
            assert.ok(card.includes('Kategori:'));
            assert.ok(elements.selected_container.html.includes('changeIncomingUnit(this,'));
            const makeRow = (unit, quantity) => {
                const fields = {
                    '[id_barang]': { value: '1' }, '[satuan]': { value: unit },
                    '[jumlah]': { value: quantity }, '[harga_satuan]': { value: '' },
                    '[harga_jual]': { value: '' },
                    '[data-incoming-conversion]': {}, '[data-stock-before]': {},
                    '[data-stock-added]': {}, '[data-stock-after]': {}
                };
                return { fields, querySelector(selector) {
                    return fields[selector] || Object.entries(fields).find(([key]) => selector.includes(key))?.[1];
                } };
            };
            const packRow = makeRow('pcs', 2);
            const pcsRow = makeRow('pcs', 3);
            let rows = [packRow, pcsRow];
            context.document.querySelectorAll = () => rows;
            context.document.querySelector = () => packRow;
            context.onItemChange = () => context.refreshIncomingStock();
            context.changeIncomingUnit({ value: 'dus' }, 0);
            assert.equal(packRow.fields['[satuan]'].value, 'dus');
            assert.equal(packRow.fields['[harga_satuan]'].value, '12.000');
            assert.equal(packRow.fields['[harga_jual]'].value, '24.000');
            assert.equal(packRow.fields['[data-stock-before]'].textContent, '100 pcs');
            assert.equal(packRow.fields['[data-stock-added]'].textContent, '+ 24 pcs');
            assert.equal(packRow.fields['[data-stock-after]'].textContent, '127 pcs');
            assert.equal(pcsRow.fields['[data-stock-after]'].textContent, '127 pcs');
            packRow.fields['[jumlah]'].value = 4;
            context.refreshIncomingStock();
            assert.equal(packRow.fields['[data-stock-after]'].textContent, '151 pcs');
            rows = [pcsRow];
            context.refreshIncomingStock();
            assert.equal(pcsRow.fields['[data-stock-after]'].textContent, '103 pcs');
            assert.equal(products[0].stok, 100, 'Preview must not mutate saved stock');
        }
        if (view.startsWith('penjualan')) {
            let selected;
            context.openTambahStokModal = barang => { selected = barang; };
            context.openTambahStokFromButton(button);
            assert.equal(selected.nama_barang, products[0].nama_barang);
        }
    });
}
