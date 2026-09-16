const { test } = require('node:test');
const assert = require('node:assert/strict');
const units = require('../public/assets/js/stock-units.js');
test('mixed quantities are aggregated in base units per product', () => {
    const rows = [{ id:1, quantity:1, nilai:100 }, { id:1, quantity:2, nilai:12 }, { id:1, quantity:3, nilai:1 }, { id:1, quantity:1, nilai:6 }, { id:2, quantity:10, nilai:10 }];
    assert.equal(units.required(rows, 1),133);
    assert.equal(units.required(rows, 2),100);
    assert.ok(units.required(rows, 1) > 130);
});
test('base label and edit stock use conversion rather than master label or raw quantities', () => {
    const barang = { id_barang:1, stok:3, satuan:'pack', satuan_detail:[{satuan:'pack',nilai:12},{satuan:'botol',nilai:1}] };
    assert.equal(units.baseLabel(barang),'botol');
    assert.equal(units.available(barang,[{id_barang:1,jumlah:2,nilai_satuan:12},{id_barang:1,jumlah:1,nilai_satuan:100}]),127);
});
