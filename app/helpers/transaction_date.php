<?php

function transactionDate($value, ?string $fallback = null): string {
    $raw = trim((string)$value);
    if ($raw === '') $raw = $fallback ?? date('Y-m-d');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
    if (!$date || $date->format('Y-m-d') !== $raw) {
        throw new InvalidArgumentException('Tanggal transaksi tidak valid. Gunakan tanggal kalender yang benar.');
    }
    return $raw;
}
