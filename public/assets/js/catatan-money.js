(function () {
    ['uang_dikeluarkan', 'uang_di_kasir'].forEach(function (id) {
        const input = document.getElementById(id);
        if (!input) return;
        // Normalize restored drafts, including decimal values from the database.
        if (/^[\d.,\s]*$/.test(input.value)) input.value = MoneyID.format(input.value);
        input.addEventListener('input', function () {
            // Keep incomplete decimals while typing; invalid input is validated by the server.
            if (!/^[\d.]*([,]\d{0,2})?$/.test(input.value)) return;
            const position = input.selectionStart ?? input.value.length;
            const before = input.value.slice(0, position).replace(/\./g, '').length;
            const parts = input.value.replace(/\./g, '').split(',');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = parts.join(',');
            let caret = 0;
            let count = 0;
            while (caret < input.value.length && count < before) {
                if (input.value[caret] !== '.') count++;
                caret++;
            }
            input.setSelectionRange(caret, caret);
        });
    });
})();
