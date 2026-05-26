'use client'

import { useEffect, useState } from 'react'
import { useParams, useRouter } from 'next/navigation'
import api from '@/lib/api'

const statusColors: Record<string, string> = {
  rascunho: 'bg-gray-100 text-gray-700',
  confirmado: 'bg-blue-100 text-blue-700',
  faturado: 'bg-green-100 text-green-700',
  cancelado: 'bg-red-100 text-red-700',
}

export default function PedidoDetalhe() {
  const { id } = useParams()
  const router = useRouter()
  const [pedido, setPedido] = useState<any>(null)
  const [loading, setLoading] = useState(true)
  const [toast, setToast] = useState<string | null>(null)

  useEffect(() => {
    fetchPedido()
  }, [id])

  const fetchPedido = async () => {
    try {
      const response = await api.get(`/api/pedidos/${id}`)
      setPedido(response.data)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const showToast = (msg: string) => {
    setToast(msg)
    setTimeout(() => setToast(null), 3000)
  }

  const handleAction = async (action: string) => {
    try {
      await api.post(`/api/pedidos/${id}/${action}`)
      showToast(`Pedido ${action} com sucesso!`)
      fetchPedido()
    } catch (err: any) {
      showToast(err.response?.data?.message || `Erro ao ${action} pedido.`)
    }
  }

  if (loading) return <p className="text-gray-400">Carregando...</p>
  if (!pedido) return <p className="text-gray-400">Pedido não encontrado.</p>

  return (
    <div>
      {/* Toast */}
      {toast && (
        <div className="fixed top-4 right-4 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg z-50">
          {toast}
        </div>
      )}

      <div className="flex items-center gap-4 mb-6">
        <button onClick={() => router.back()} className="text-gray-500 hover:text-gray-700">
          ← Voltar
        </button>
        <h2 className="text-xl font-bold text-gray-900">Pedido #{pedido.id}</h2>
        <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${statusColors[pedido.status]}`}>
          {pedido.status}
        </span>
      </div>

      {/* Botões de ação */}
      <div className="flex gap-3 mb-6">
        {pedido.status === 'rascunho' && (
          <button
            onClick={() => handleAction('confirmar')}
            className="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2"
          >
            Confirmar
          </button>
        )}
        {pedido.status === 'confirmado' && (
          <button
            onClick={() => handleAction('faturar')}
            className="bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg px-4 py-2"
          >
            Faturar
          </button>
        )}
        {['rascunho', 'confirmado', 'faturado'].includes(pedido.status) && (
          <button
            onClick={() => handleAction('cancelar')}
            className="bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg px-4 py-2"
          >
            Cancelar
          </button>
        )}
      </div>

      {/* Dados do pedido */}
      <div className="grid grid-cols-2 gap-6 mb-6">
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <h3 className="font-medium text-gray-900 mb-4">Dados do Pedido</h3>
          <div className="space-y-2 text-sm">
            <div className="flex justify-between">
              <span className="text-gray-500">Cliente</span>
              <span className="font-medium">{pedido.cliente?.nome}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-500">Data Emissão</span>
              <span>{new Date(pedido.data_emissao).toLocaleDateString('pt-BR')}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-500">Frete</span>
              <span>{Number(pedido.frete).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-500">Parcelas</span>
              <span>{pedido.parcelas}x de {pedido.dias_entrada} dias</span>
            </div>
            <div className="flex justify-between font-medium">
              <span className="text-gray-900">Total Geral</span>
              <span className="text-blue-600">{Number(pedido.total_geral).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</span>
            </div>
          </div>
        </div>

        {/* NF e Contas a Receber */}
        {pedido.nf && (
          <div className="bg-white rounded-xl border border-gray-200 p-6">
            <h3 className="font-medium text-gray-900 mb-4">Nota Fiscal</h3>
            <div className="space-y-2 text-sm mb-4">
              <div className="flex justify-between">
                <span className="text-gray-500">Status NF</span>
                <span className="font-medium">{pedido.nf.status}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Total NF</span>
                <span>{Number(pedido.nf.total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</span>
              </div>
            </div>

            {pedido.nf.contas_receber?.length > 0 && (
              <>
                <h4 className="font-medium text-gray-700 mb-2 text-sm">Contas a Receber</h4>
                <div className="space-y-1">
                  {pedido.nf.contas_receber.map((conta: any) => (
                    <div key={conta.id} className="flex justify-between text-xs text-gray-600">
                      <span>Parcela {conta.parcela}/{conta.parcelas}</span>
                      <span>{new Date(conta.data_vencimento).toLocaleDateString('pt-BR')}</span>
                      <span className="font-medium">{Number(conta.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</span>
                      <span className={`px-1 rounded ${conta.status === 'aberta' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'}`}>
                        {conta.status}
                      </span>
                    </div>
                  ))}
                </div>
              </>
            )}
          </div>
        )}
      </div>

      {/* Itens do pedido */}
      <div className="bg-white rounded-xl border border-gray-200 p-6">
        <h3 className="font-medium text-gray-900 mb-4">Itens</h3>
        <table className="w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              <th className="text-left px-3 py-2 text-gray-600">Produto</th>
              <th className="text-right px-3 py-2 text-gray-600">Qtd</th>
              <th className="text-right px-3 py-2 text-gray-600">Preço Unit.</th>
              <th className="text-right px-3 py-2 text-gray-600">Desconto</th>
              <th className="text-right px-3 py-2 text-gray-600">Total</th>
            </tr>
          </thead>
          <tbody>
            {pedido.itens?.map((item: any) => (
              <tr key={item.id} className={`border-t border-gray-100 ${!item.ativo ? 'opacity-40' : ''}`}>
                <td className="px-3 py-2">{item.produto?.descricao}</td>
                <td className="px-3 py-2 text-right">{item.quantidade}</td>
                <td className="px-3 py-2 text-right">{Number(item.preco_unitario).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                <td className="px-3 py-2 text-right">{Number(item.desconto_unitario).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
                <td className="px-3 py-2 text-right font-medium">{Number(item.total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}