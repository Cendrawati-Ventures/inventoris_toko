const { test } = require('node:test');
const assert = require('node:assert/strict');
const money = require('../public/assets/js/money.js');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const cases = [[60000,60000],['60000.00',60000],['60000',60000],['60.000',60000],['Rp 60.000,00',60000],['60.000,50',60000.5],['60000.50',60000.5],['6.000.000',6000000],['6000000.00',6000000],['3833.33',3833.33]];
test('typing unit prices keeps thousands instead of turning them into decimals', () => {
    for (const digits of ['15000', '26700', '60000', '15000000']) {
        let displayed = '0';
        for (const digit of digits) displayed = money.formatInput(displayed + digit);
        assert.equal(displayed, Number(digits).toLocaleString('id-ID'));
        assert.equal(money.parse(displayed), Number(digits));
    }
    assert.equal(money.formatInput('15.0000'), '150.000');
    assert.equal(money.formatInput('15.000,'), '15.000,');
    assert.equal(money.formatInput('15.000,50'), '15.000,50');
    assert.equal(money.formatInput(''), '');
});
test('database decimals, Indonesian thousands and decimals retain value through format/save', () => {
    for (const [input,expected] of cases) {
        assert.equal(money.parse(input),expected,String(input));
        assert.equal(money.parse(money.format(input)),expected,String(input));
    }
    assert.equal(money.format('60000.00'),'60.000');
});
test('PHP controller money parser agrees with browser before persistence', () => {
    const script = 'require $argv[1]; $cases=json_decode($argv[2],true); echo json_encode(array_map(fn($case)=>parseMoneyInput($case[0]),$cases));';
    const output = execFileSync('php',['-r',script,path.join(__dirname,'../app/helpers/money.php'),JSON.stringify(cases)],{encoding:'utf8'});
    assert.deepEqual(JSON.parse(output),cases.map(row=>row[1]));
});
