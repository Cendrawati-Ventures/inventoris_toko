<?php

// Stock is already stored in base quantities: never multiply it by packaging size again.
function inventoryBasePrices(array $item): array {
    $units = $item['satuan_detail'] ?? [];
    if (is_string($units)) $units = json_decode($units, true);
    if (!is_array($units)) return $item;
    foreach ($units as $unit) {
        if (!is_array($unit)) continue;
        $factor = $unit['nilai'] ?? $unit['nilai_satuan'] ?? $unit['konversi'] ?? 1;
        if ((float)$factor !== 1.0) continue;
        foreach (['harga_beli', 'harga_jual'] as $field) {
            if (isset($unit[$field]) && is_numeric($unit[$field])) $item[$field] = (float)$unit[$field];
        }
        $item['satuan'] = $unit['satuan'] ?? $item['satuan'];
        break;
    }
    return $item;
}

function inventoryTotals(array $items): array {
    $totals = ['total_harga_beli' => 0.0, 'total_harga_jual' => 0.0, 'total_stok' => 0.0];
    foreach ($items as $item) {
        $item = inventoryBasePrices($item);
        $qty = (float)($item['stok'] ?? 0);
        $totals['total_stok'] += $qty;
        $totals['total_harga_beli'] += $qty * (float)($item['harga_beli'] ?? 0);
        $totals['total_harga_jual'] += $qty * (float)($item['harga_jual'] ?? 0);
    }
    return $totals;
}

function inventoryCategoryTotals(array $items): array {
    $groups = [];
    foreach ($items as $item) $groups[(string)($item['id_kategori'] ?? '')][] = $item;
    $result = [];
    foreach ($groups as $group) {
        $result[] = array_merge(inventoryTotals($group), [
            'id_kategori' => $group[0]['id_kategori'] ?? null,
            'nama_kategori' => $group[0]['nama_kategori'] ?? null,
        ]);
    }
    usort($result, fn($a, $b) => strcmp($a['nama_kategori'] ?? '', $b['nama_kategori'] ?? ''));
    return $result;
}
