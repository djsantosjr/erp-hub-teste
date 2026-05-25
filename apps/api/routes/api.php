<?php

use App\Http\Controllers\Api\PedidoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('pedidos', PedidoController::class);
    Route::post('pedidos/{pedido}/confirmar', [PedidoController::class, 'confirmar']);
    Route::post('pedidos/{pedido}/faturar', [PedidoController::class, 'faturar']);
    Route::post('pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar']);
});