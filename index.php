<?php

use Illuminate\Http\Request;

// Tangkap semua output liar (spasi/baris kosong dari file PHP lain) supaya tidak ikut
// masuk ke file unduhan (Excel, SVG, dll.) dan membuatnya rusak.
ob_start();

define('LARAVEL_START', microtime(true));

// Mode maintenance
if (file_exists($maintenance = __DIR__.'/laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader (folder project Laravel ada di /htdocs/laravel)
require __DIR__.'/laravel/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/laravel/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$response = $kernel->handle($request);

// Buang output liar yang muncul sebelum respons dikirim.
// (Cek level buffer dulu: ada controller yang menutup buffer sendiri, mis. pembuat QR.)
while (ob_get_level() > 1) {
    ob_end_clean();
}
if (ob_get_level() > 0) {
    ob_clean();
}

$response->send();
$kernel->terminate($request, $response);