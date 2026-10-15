<?php
/**
 * HostelERP Desktop App Secure Download Dispatcher
 * Routes downloads to verified GitHub release assets or graceful custom 404 error page.
 */

require_once __DIR__ . '/security_config.php';

$current_version = '2.0.1';
$github_release_base = 'https://github.com/arpitchaudhary14/HostelERP/releases/download/v' . $current_version;

// Whitelist of valid release assets for v2.0.1
$release_assets = [
    // Windows builds
    'HostelERP-Setup-2.0.1.exe' => [
        'os' => 'Windows',
        'type' => 'Installer (.exe)',
        'arch' => 'x64',
        'size' => '148 MB'
    ],
    'HostelERP-2.0.1.exe' => [
        'os' => 'Windows',
        'type' => 'Portable (.exe)',
        'arch' => 'x64',
        'size' => '73 MB'
    ],
    'HostelERP-2.0.1-win.zip' => [
        'os' => 'Windows',
        'type' => 'Portable Archive (.zip)',
        'arch' => 'x64',
        'size' => '99 MB'
    ],
    'HostelERP-2.0.1-arm64-win.zip' => [
        'os' => 'Windows',
        'type' => 'Portable Archive (.zip)',
        'arch' => 'arm64',
        'size' => '99 MB'
    ],

    // macOS builds
    'HostelERP-2.0.1-arm64.dmg' => [
        'os' => 'macOS',
        'type' => 'Disk Image (.dmg)',
        'arch' => 'Apple Silicon (M1/M2/M3/M4)',
        'size' => '90 MB'
    ],
    'HostelERP-2.0.1.dmg' => [
        'os' => 'macOS',
        'type' => 'Disk Image (.dmg)',
        'arch' => 'Intel x64',
        'size' => '95 MB'
    ],

    // Linux builds
    'hostelerp-desktop_2.0.1_amd64.deb' => [
        'os' => 'Linux',
        'type' => 'Debian / Ubuntu (.deb)',
        'arch' => 'x64 (amd64)',
        'size' => '69 MB'
    ],
    'hostelerp-desktop_2.0.1_arm64.deb' => [
        'os' => 'Linux',
        'type' => 'Debian / Ubuntu (.deb)',
        'arch' => 'arm64',
        'size' => '65 MB'
    ],
    'HostelERP-2.0.1.AppImage' => [
        'os' => 'Linux',
        'type' => 'Universal Portable (.AppImage)',
        'arch' => 'x64',
        'size' => '99 MB'
    ],
    'HostelERP-2.0.1-arm64.AppImage' => [
        'os' => 'Linux',
        'type' => 'Universal Portable (.AppImage)',
        'arch' => 'arm64',
        'size' => '99 MB'
    ]
];

// Smart alias map to automatically upgrade any legacy 1.0.9 and 2.0.0 requests to 2.0.1
$alias_map = [
    // 2.0.0 requests -> 2.0.1
    'HostelERP-Setup-2.0.0.exe' => 'HostelERP-Setup-2.0.1.exe',
    'HostelERP-2.0.0.exe' => 'HostelERP-2.0.1.exe',
    'HostelERP-2.0.0-win.zip' => 'HostelERP-2.0.1-win.zip',
    'HostelERP-2.0.0-arm64-win.zip' => 'HostelERP-2.0.1-arm64-win.zip',
    'HostelERP-2.0.0-arm64.dmg' => 'HostelERP-2.0.1-arm64.dmg',
    'HostelERP-2.0.0.dmg' => 'HostelERP-2.0.1.dmg',
    'hostelerp-desktop_2.0.0_amd64.deb' => 'hostelerp-desktop_2.0.1_amd64.deb',
    'hostelerp-desktop_2.0.0_arm64.deb' => 'hostelerp-desktop_2.0.1_arm64.deb',
    'HostelERP-2.0.0.AppImage' => 'HostelERP-2.0.1.AppImage',
    'HostelERP-2.0.0-arm64.AppImage' => 'HostelERP-2.0.1-arm64.AppImage',

    // 1.0.9 requests -> 2.0.1
    'HostelERP-Setup-1.0.9.exe' => 'HostelERP-Setup-2.0.1.exe',
    'HostelERP-1.0.9.exe' => 'HostelERP-2.0.1.exe',
    'HostelERP-1.0.9-win.zip' => 'HostelERP-2.0.1-win.zip',
    'HostelERP-1.0.9-arm64-win.zip' => 'HostelERP-2.0.1-arm64-win.zip',
    'HostelERP-1.0.9-arm64.dmg' => 'HostelERP-2.0.1-arm64.dmg',
    'HostelERP-1.0.9.dmg' => 'HostelERP-2.0.1.dmg',
    'hostelerp-desktop_1.0.9_amd64.deb' => 'hostelerp-desktop_2.0.1_amd64.deb',
    'hostelerp-desktop_1.0.9_arm64.deb' => 'hostelerp-desktop_2.0.1_arm64.deb',
    'HostelERP_1.0.9_amd64.deb' => 'hostelerp-desktop_2.0.1_amd64.deb',
    'HostelERP_1.0.9_arm64.deb' => 'hostelerp-desktop_2.0.1_arm64.deb',
    'HostelERP-1.0.9.AppImage' => 'HostelERP-2.0.1.AppImage',
    'HostelERP-1.0.9-arm64.AppImage' => 'HostelERP-2.0.1-arm64.AppImage',
    'HostelERP-Setup-2.0.1-arm64.exe' => 'HostelERP-2.0.1-arm64-win.zip',
    'HostelERP_2.0.1_amd64.deb' => 'hostelerp-desktop_2.0.1_amd64.deb',
    'HostelERP_2.0.1_arm64.deb' => 'hostelerp-desktop_2.0.1_arm64.deb',
];

$requested_file = trim($_GET['file'] ?? '');

// Also support query by platform params (e.g. ?os=windows&type=installer)
if (empty($requested_file)) {
    $os = strtolower(trim($_GET['os'] ?? ''));
    $arch = strtolower(trim($_GET['arch'] ?? 'x64'));
    $type = strtolower(trim($_GET['type'] ?? ''));

    if ($os === 'windows') {
        if ($type === 'zip' || $type === 'portable_zip') {
            $requested_file = ($arch === 'arm64') ? 'HostelERP-2.0.1-arm64-win.zip' : 'HostelERP-2.0.1-win.zip';
        } elseif ($type === 'portable' || $type === 'exe_portable') {
            $requested_file = 'HostelERP-2.0.1.exe';
        } else {
            $requested_file = 'HostelERP-Setup-2.0.1.exe';
        }
    } elseif ($os === 'macos' || $os === 'mac') {
        $requested_file = ($arch === 'arm64' || $arch === 'm1' || $arch === 'm2' || $arch === 'apple_silicon')
            ? 'HostelERP-2.0.1-arm64.dmg'
            : 'HostelERP-2.0.1.dmg';
    } elseif ($os === 'linux') {
        if ($type === 'appimage') {
            $requested_file = ($arch === 'arm64') ? 'HostelERP-2.0.1-arm64.AppImage' : 'HostelERP-2.0.1.AppImage';
        } else {
            $requested_file = ($arch === 'arm64') ? 'hostelerp-desktop_2.0.1_arm64.deb' : 'hostelerp-desktop_2.0.1_amd64.deb';
        }
    }
}

// Check alias
if (isset($alias_map[$requested_file])) {
    $requested_file = $alias_map[$requested_file];
}

// If file is valid in whitelist, redirect directly to GitHub Release asset
if (!empty($requested_file) && isset($release_assets[$requested_file])) {
    $target_url = $github_release_base . '/' . rawurlencode($requested_file);
    header('Location: ' . $target_url, true, 302);
    exit;
}

// If not found or unsupported architecture (e.g., ia32 / 32-bit or nonexistent file),
// render our custom 404 page instead of letting GitHub show its 404!
$code = 404;
$display_version = '2.0.1';
$custom_title = "Download Package Not Found";
$custom_desc = !empty($requested_file)
    ? "The requested desktop package ('" . htmlspecialchars($requested_file) . "') is not available for HostelERP v" . $display_version . ". 32-bit architecture is no longer supported by HostelERP. Please check the available 64-bit download options or access HostelERP via your web browser."
    : "No download package specified. Please choose a package from the desktop download center.";

if (file_exists(__DIR__ . '/error.php')) {
    include __DIR__ . '/error.php';
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>" . htmlspecialchars($custom_desc) . "</p>";
}
exit;
