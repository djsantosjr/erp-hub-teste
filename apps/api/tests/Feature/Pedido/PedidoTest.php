<?php

use App\Domain\Comercial\Actions\FaturarPedidoAction;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;

// ============================================================
// HELPERS
// ============================================================

function criarEmpresa(int $id, string $nome): Empresa
{
    return Empresa::create([
        'id'   => $id,
        'nome' => $nome,
        'cnpj' => "0{$id}.000.000/0001-0{$id}",
        'ativo' => true,
    ]);
}

function criarUsuario(Empresa $empresa, string $role): User
{
    $permissions = [
        'pedidos.ver', 'pedidos.criar', 'pedidos.editar',
        'pedidos.confirmar', 'pedidos.faturar', 'pedidos.cancelar',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $r = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

    if ($role === 'operador') {
        $r->syncPermissions(['pedidos.ver','pedidos.criar','pedidos.editar','pedidos.confirmar','pedidos.cancelar']);
    } elseif ($role === 'comercial_faturador') {
        $r->syncPermissions($permissions);
    }

    $user = User::factory()->create(['idempresa_default' => $empresa->id]);
    $user->empresas()->attach($empresa->id);

    setPermissionsTeamId($empresa->id);
    $user->assignRole($role);

    return $user;
}

function criarPedidoComItens(Empresa $empresa, User $user, array $opcoes = []): Pedido
{
    $cliente = Cliente::create([
        'idempresa' => $empresa->id,
        'nome'      => 'Cliente Teste',
        'documento' => '11.111.111/0001-11',
        'ativo'     => true,
    ]);

    $produto = Produto::create([
        'idempresa'      => $empresa->id,
        'codigo'         => 'P-001',
        'descricao'      => 'Produto Teste',
        'preco_unitario' => 100.00,
        'ativo'          => true,
    ]);

    $pedido = Pedido::create(array_merge([
        'idempresa'    => $empresa->id,
        'cliente_id'   => $cliente->id,
        'data_emissao' => now()->toDateString(),
        'status'       => 'rascunho',
        'frete'        => 10.00,
        'parcelas'     => 1,
        'dias_entrada' => 28,
        'intervalo'    => 28,
    ], $opcoes));

    PedidoItem::create([
        'pedido_id'         => $pedido->id,
        'produto_id'        => $produto->id,
        'quantidade'        => 2,
        'preco_unitario'    => 100.00,
        'desconto_unitario' => 0,
        'total'             => 200.00,
        'ativo'             => true,
    ]);

    return $pedido;
}

// ============================================================
// TESTES
// ============================================================

it('cria pedido com itens e calcula total corretamente', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $cliente = Cliente::create([
        'idempresa' => $empresa->id,
        'nome'      => 'Cliente X',
        'documento' => '11.111.111/0001-11',
        'ativo'     => true,
    ]);

    $produto = Produto::create([
        'idempresa'      => $empresa->id,
        'codigo'         => 'P-001',
        'descricao'      => 'Produto X',
        'preco_unitario' => 50.00,
        'ativo'          => true,
    ]);

    // R1: total = (preco - desconto) * quantidade = (50 - 5) * 3 = 135.00
    $response = postJson('/api/pedidos', [
        'cliente_id'   => $cliente->id,
        'data_emissao' => now()->toDateString(),
        'frete'        => 0,
        'parcelas'     => 1,
        'dias_entrada' => 28,
        'intervalo'    => 28,
        'itens'        => [[
            'produto_id'        => $produto->id,
            'quantidade'        => 3,
            'preco_unitario'    => 50.00,
            'desconto_unitario' => 5.00,
            'ativo'             => true,
        ]],
    ]);

    $response->assertStatus(201);
    $item = PedidoItem::first();
    expect((float) $item->total)->toBe(135.00);
});

it('nao permite confirmar pedido sem itens ativos', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $user);
    $pedido->itens()->update(['ativo' => false]);

    $response = postJson("/api/pedidos/{$pedido->id}/confirmar");
    $response->assertStatus(422);
});

it('fatura pedido criando NF rascunho com frete prorrateado ao centavo', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $cliente = Cliente::create(['idempresa' => $empresa->id, 'nome' => 'CLI', 'ativo' => true]);
    $prod1   = Produto::create(['idempresa' => $empresa->id, 'codigo' => 'P1', 'descricao' => 'P1', 'preco_unitario' => 100, 'ativo' => true]);
    $prod2   = Produto::create(['idempresa' => $empresa->id, 'codigo' => 'P2', 'descricao' => 'P2', 'preco_unitario' => 200, 'ativo' => true]);

    $pedido = Pedido::create([
        'idempresa' => $empresa->id, 'cliente_id' => $cliente->id,
        'data_emissao' => now()->toDateString(), 'status' => 'confirmado',
        'frete' => 10.00, 'parcelas' => 1, 'dias_entrada' => 28, 'intervalo' => 28,
    ]);

    PedidoItem::create(['pedido_id' => $pedido->id, 'produto_id' => $prod1->id, 'quantidade' => 1, 'preco_unitario' => 100, 'desconto_unitario' => 0, 'total' => 100, 'ativo' => true]);
    PedidoItem::create(['pedido_id' => $pedido->id, 'produto_id' => $prod2->id, 'quantidade' => 1, 'preco_unitario' => 200, 'desconto_unitario' => 0, 'total' => 200, 'ativo' => true]);

    $nf = (new FaturarPedidoAction)->execute($pedido);

    // R2: frete 10 / subtotal 300
    // item1: round(100/300 * 10, 2) = 3.33
    // item2: 10 - 3.33 = 6.67 (residuo)
    $itens = $nf->itens()->orderBy('numero_item')->get();
    expect((float) $itens[0]->frete_rateado)->toBe(3.33);
    expect((float) $itens[1]->frete_rateado)->toBe(6.67);
    expect((float)($itens[0]->frete_rateado + $itens[1]->frete_rateado))->toBe(10.00);
});

it('fatura pedido gerando N parcelas com datas corretas e residuo na ultima', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $user, [
        'status'       => 'confirmado',
        'frete'        => 0,
        'parcelas'     => 3,
        'dias_entrada' => 30,
        'intervalo'    => 30,
    ]);

    $nf = (new FaturarPedidoAction)->execute($pedido);

    // R3: total = 200, 3 parcelas
    // parcela 1 e 2 = round(200/3, 2) = 66.67
    // parcela 3 = 200 - 133.34 = 66.66
    $parcelas = $nf->contasReceber()->orderBy('parcela')->get();

    expect($parcelas)->toHaveCount(3);
    expect((float) $parcelas[0]->valor)->toBe(66.67);
    expect((float) $parcelas[1]->valor)->toBe(66.67);
    expect((float) $parcelas[2]->valor)->toBe(66.66);

    // Soma deve fechar exato no total
    $soma = $parcelas->sum('valor');
    expect(round((float) $soma, 2))->toBe(200.00);

    // Datas corretas
    $emissao = \Carbon\Carbon::parse($pedido->data_emissao);
    expect($parcelas[0]->data_vencimento->format('Y-m-d'))->toBe($emissao->copy()->addDays(30)->format('Y-m-d'));
    expect($parcelas[1]->data_vencimento->format('Y-m-d'))->toBe($emissao->copy()->addDays(60)->format('Y-m-d'));
    expect($parcelas[2]->data_vencimento->format('Y-m-d'))->toBe($emissao->copy()->addDays(90)->format('Y-m-d'));
});

it('nao fatura pedido se action falhar transacao atomica', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $user, ['status' => 'confirmado', 'frete' => 0]);

    // Força falha na transação mockando DB
    DB::shouldReceive('transaction')->once()->andThrow(new \Exception('Falha simulada'));

    expect(fn () => (new FaturarPedidoAction)->execute($pedido))
        ->toThrow(\Exception::class);

    // Pedido não deve ter mudado de status
    expect($pedido->fresh()->status)->toBe('confirmado');
});

it('nao permite ver pedido de outro tenant mesmo injetando X-Empresa-Id', function () {
    $empresaA = criarEmpresa(1, 'Empresa A');
    $empresaB = criarEmpresa(2, 'Empresa B');
    $userA    = criarUsuario($empresaA, 'comercial_faturador');
    $userB    = criarUsuario($empresaB, 'comercial_faturador');

    actingAs($userA);
    setPermissionsTeamId($empresaA->id);

    // Cria pedido na Empresa B
    $cliente = Cliente::create(['idempresa' => $empresaB->id, 'nome' => 'CLI B', 'ativo' => true]);
    $pedidoB = Pedido::withoutGlobalScopes()->create([
        'idempresa'    => $empresaB->id,
        'cliente_id'   => $cliente->id,
        'data_emissao' => now()->toDateString(),
        'status'       => 'rascunho',
        'frete'        => 0,
        'parcelas'     => 1,
        'dias_entrada' => 28,
        'intervalo'    => 28,
    ]);

    // Usuário A tenta ver pedido da Empresa B injetando header
    $response = getJson("/api/pedidos/{$pedidoB->id}", [
        'X-Empresa-Id' => $empresaB->id,
    ]);

    $response->assertStatus(404);
});

it('nao permite operador faturar pedido RBAC', function () {
    $empresa  = criarEmpresa(1, 'Empresa A');
    $operador = criarUsuario($empresa, 'operador');

    actingAs($operador);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $operador, ['status' => 'confirmado']);

    $response = postJson("/api/pedidos/{$pedido->id}/faturar");
    $response->assertStatus(403);
});

it('faturar duas vezes nao cria duas NFs idempotencia', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $user, ['status' => 'confirmado', 'frete' => 0]);

    // Primeira vez — fatura normalmente
    $nf1 = (new FaturarPedidoAction)->execute($pedido);

    // Segunda vez — pedido agora está 'faturado', mas a idempotência
    // deve retornar a NF existente sem criar outra
    $pedidoFaturado = $pedido->fresh();

    // Força status confirmado para simular chamada dupla antes do status atualizar
    $pedidoFaturado->status = 'confirmado';

    $nf2 = (new FaturarPedidoAction)->execute($pedidoFaturado);

    expect($nf1->id)->toBe($nf2->id);
    expect(\App\Models\Nf::count())->toBe(1);
});

it('cancela pedido faturado se NF rascunho e parcelas abertas', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $user, ['status' => 'confirmado', 'frete' => 0]);

    (new FaturarPedidoAction)->execute($pedido);
    $pedido->refresh();

    $response = postJson("/api/pedidos/{$pedido->id}/cancelar");
    $response->assertStatus(200);

    expect($pedido->fresh()->status)->toBe('cancelado');
    expect(\App\Models\Nf::first()->status)->toBe('cancelada');
    expect(\App\Models\ContaReceber::first()->status)->toBe('cancelada');
});

it('nao cancela pedido se NF ja autorizada mock', function () {
    $empresa = criarEmpresa(1, 'Empresa A');
    $user    = criarUsuario($empresa, 'comercial_faturador');

    actingAs($user);
    setPermissionsTeamId($empresa->id);

    $pedido = criarPedidoComItens($empresa, $user, ['status' => 'confirmado', 'frete' => 0]);

    (new FaturarPedidoAction)->execute($pedido);
    $pedido->refresh();

    // Simula NF já autorizada
    \App\Models\Nf::first()->update(['status' => 'autorizada']);

    $response = postJson("/api/pedidos/{$pedido->id}/cancelar");
    $response->assertStatus(422);
});