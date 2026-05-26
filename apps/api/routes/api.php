<?php

use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Rotas públicas de auth
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('pedidos', PedidoController::class);
    Route::post('pedidos/{pedido}/confirmar', [PedidoController::class, 'confirmar']);
    Route::post('pedidos/{pedido}/faturar', [PedidoController::class, 'faturar']);
    Route::post('pedidos/{pedido}/cancelar', [PedidoController::class, 'cancelar']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('produtos', ProdutoController::class);
});