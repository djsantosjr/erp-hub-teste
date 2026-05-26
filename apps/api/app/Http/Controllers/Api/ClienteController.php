<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function index(): JsonResponse
    {
        $clientes = Cliente::where('ativo', true)->orderBy('nome')->get();
        return response()->json($clientes);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json($cliente);
    }
}