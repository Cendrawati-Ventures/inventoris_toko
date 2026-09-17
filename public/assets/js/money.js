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
        formatInput(value) {
            // In an Indonesian input, dots already inserted while typing are grouping marks.
            // Parsing '1.5000' as a database decimal would incorrectly turn 15000 into 1.5.
            const text = String(value ?? '');
            if (!/^-?[\d.]*([,]\d{0,2})?$/.test(text)) return text;
            const parts = text.replace(/\./g, '').split(',');
            parts[0] = parts[0].replace(/^(-?)0+(?=\d)/, '$1').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return parts.join(',');
        },
        format(value) {
            if (String(value ?? '').trim() === '') return '';
            return parse(value).toLocaleString('id-ID', { maximumFractionDigits: 2 });
        }
    };
    root.MoneyID = money;
    if (typeof module !== 'undefined') module.exports = money;
})(globalThis);
