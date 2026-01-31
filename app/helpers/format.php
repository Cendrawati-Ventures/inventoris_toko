<?php

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $pecah = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

function formatTanggalWaktu($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    $pecah = explode('-', date('Y-m-d', $timestamp));
    $waktu = date('H:i', $timestamp);
    
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0] . ' ' . $waktu;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function alert($message, $type = 'success') {
    $bgColor = $type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    return '<div class="' . $bgColor . ' border px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">' . htmlspecialchars($message) . '</span>
    </div>';
}
