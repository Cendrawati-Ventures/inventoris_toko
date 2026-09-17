const assert = require('node:assert/strict');
const test = require('node:test');
const fs = require('node:fs');
const vm = require('node:vm');
const MoneyID = require('../public/assets/js/money.js');

test('cashier amounts group while typing and preserve decimals, empty values and caret', () => {
    const fields = {};
    for (const id of ['uang_dikeluarkan', 'uang_di_kasir']) {
        fields[id] = {
            value: id === 'uang_dikeluarkan' ? '60000.00' : '',
            addEventListener(event, fn) { this[event] = fn; },
            setSelectionRange(start) { this.selectionStart = start; }
        };
    }
    vm.runInNewContext(fs.readFileSync(require.resolve('../public/assets/js/catatan-money.js'), 'utf8'), {
        MoneyID, document: { getElementById: id => fields[id] }
    });
    assert.equal(fields.uang_dikeluarkan.value, '60.000');
    assert.equal(fields.uang_di_kasir.value, '');
    for (const field of Object.values(fields)) {
        for (const [raw, expected] of [['60000', '60.000'], ['60.0000', '600.000'], ['60000,', '60.000,'], ['60000,50', '60.000,50'], ['', ''], ['0', '0'], ['-1', '-1']]) {
            field.value = raw;
            field.selectionStart = raw.length;
            field.input();
            assert.equal(field.value, expected);
            assert.equal(field.selectionStart, expected.length);
        }
        field.value = '601.000';
        field.selectionStart = 3;
        field.input();
        assert.equal(field.selectionStart, 3, 'Caret stays beside the digit being edited');
    }
});
