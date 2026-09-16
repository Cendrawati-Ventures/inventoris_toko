(function (root) {
    function parse(value) {
        if (typeof value === 'number') return Number.isFinite(value) ? value : 0;
        let text = String(value ?? '').trim().replace(/Rp\.?\s*/gi, '').replace(/\s/g, '');
        if (!text) return 0;
        if (text.includes(',')) {
            // Indonesian input: 60.000,50. Also accept decimal-dot grouped input.
            text = text.lastIndexOf(',') > text.lastIndexOf('.')
                ? text.replace(/\./g, '').replace(',', '.')
                : text.replace(/,/g, '');
        } else if (/^-?\d{1,3}(\.\d{3})+$/.test(text)) {
            text = text.replace(/\./g, '');
        }
        const number = Number(text);
        return Number.isFinite(number) ? number : 0;
    }
    const money = {
        parse,
        format(value) {
            if (String(value ?? '').trim() === '') return '';
            return parse(value).toLocaleString('id-ID', { maximumFractionDigits: 2 });
        }
    };
    root.MoneyID = money;
    if (typeof module !== 'undefined') module.exports = money;
})(globalThis);
