<?php

// Accept database decimals and Indonesian formatted money without multiplying cents.
function parseMoneyInput($value): ?float {
    if (is_int($value) || is_float($value)) return is_finite((float)$value) ? (float)$value : null;
    $text = preg_replace('/Rp\.?\s*|\s/iu', '', trim((string)$value));
    if ($text === '') return null;
    if (strpos($text, ',') !== false) {
        $dot = strrpos($text, '.');
        $text = $dot === false || strrpos($text, ',') > $dot
            ? str_replace(',', '.', str_replace('.', '', $text))
            : str_replace(',', '', $text);
    } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $text)) {
        $text = str_replace('.', '', $text);
    }
    return is_numeric($text) && is_finite((float)$text) ? (float)$text : null;
}
