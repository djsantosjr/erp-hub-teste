<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;

class ProdutoController extends Controller
{
    public function index(): JsonResponse
    {
        $produtos = Produto::where('ativo', true)->orderBy('descricao')->get();
        return response()->json($produtos);
    }

    public function show(Produto $produto): JsonResponse
    {
        return response()->json($produto);
    }
}