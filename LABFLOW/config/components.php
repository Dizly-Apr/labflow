<?php
/**
 * Daftar jenis komponen PC - SATU sumber untuk PHP dan JS.
 * Menambah jenis baru cukup menambah satu baris di sini (kolom component_type berupa VARCHAR).
 * Format: kode => [label, ikon lucide, contoh spesifikasi, komponen inti (otomatis ada di PC baru)]
 */
function component_types(): array
{
    return [
        'cpu'         => ['Processor (CPU)',   'cpu',          'Intel Core i5-12400F, 6 Cores / 12 Threads', true],
        'ram'         => ['Memory (RAM)',      'memory-stick', '16GB DDR4 3200MHz',                          true],
        'gpu'         => ['Graphics (GPU)',    'gauge',        'NVIDIA GeForce GTX 1650 4GB',                true],
        'storage'     => ['Storage',           'hard-drive',   '512GB NVMe SSD',                             true],
        'motherboard' => ['Motherboard',       'circuit-board','ASUS PRIME B660M-K',                         false],
        'psu'         => ['Power Supply (PSU)','plug-zap',     'Corsair CV550 550W',                         false],
        'monitor'     => ['Monitor',           'monitor',      'LG 22MP48 22" IPS Full HD',                  false],
        'keyboard'    => ['Keyboard',          'keyboard',     'Logitech K120',                              false],
        'mouse'       => ['Mouse',             'mouse',        'Logitech G102 Lightsync',                    false],
        'network'     => ['Jaringan (LAN/WiFi)','network',     'Realtek Gigabit LAN onboard',                false],
    ];
}

function component_label(string $type): string
{
    return component_types()[$type][0] ?? strtoupper($type);
}

function is_component_type(string $type): bool
{
    return isset(component_types()[$type]);
}

/** Urutan tampil = urutan di daftar di atas. */
function component_order(): array
{
    return array_keys(component_types());
}

/** Data ringkas untuk JS: {cpu:{label,icon,example}, ...} */
function component_catalog_json(): array
{
    $o = [];
    foreach (component_types() as $k => [$label, $icon, $example]) {
        $o[$k] = ['label' => $label, 'icon' => $icon, 'example' => $example];
    }
    return $o;
}
