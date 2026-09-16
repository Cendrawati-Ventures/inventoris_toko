(function (root) {
    const units = {
        baseLabel(barang) {
            let detail = barang.satuan_detail || [];
            if (typeof detail === 'string') {
                try { detail = JSON.parse(detail); } catch { detail = []; }
            }
            if (!Array.isArray(detail)) detail = [];
            const base = detail.find(unit => Number(unit.nilai ?? unit.nilai_satuan ?? 1) === 1);
            return base?.satuan || barang.satuan || 'pcs';
        },
        factor(unit) {
            const value = Number(unit?.nilai ?? unit?.nilai_satuan ?? 1);
            return Number.isFinite(value) && value > 0 ? value : 1;
        },
        required(rows, id) {
            return rows.filter(row => String(row.id) === String(id))
                .reduce((sum, row) => sum + Number(row.quantity || 0) * units.factor(row), 0);
        },
        available(barang, original = []) {
            return Number(barang.stok || 0) + original
                .filter(row => String(row.id_barang) === String(barang.id_barang))
                .reduce((sum, row) => sum + Number(row.jumlah || 0) * units.factor(row), 0);
        }
    };
    root.StockUnits = units;
    if (typeof module !== 'undefined') module.exports = units;
})(globalThis);
