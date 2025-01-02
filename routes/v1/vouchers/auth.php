<?php

use App\Http\Controllers\Vouchers\DeleteVoucherHandler;
use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(function () {
    Route::get('/', GetVouchersHandler::class); // Ruta para obtener vouchers
    Route::post('/', StoreVouchersHandler::class); // Ruta para almacenar vouchers

    Route::get('/total-amounts', [GetVouchersHandler::class, 'getTotalAmounts']);
    Route::delete('/{id}', DeleteVoucherHandler::class);
});
