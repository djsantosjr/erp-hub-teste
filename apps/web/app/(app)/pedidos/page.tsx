'use client'

import { useEffect, useState } from 'react'
import { useRouter } from 'next/navigation'
import api from '@/lib/api'

interface Pedido {
  id: number
  cliente: { nome: string }
  data_emissao: string
  status: string
  total_geral: number
}

const statusColors: Record<string, string> = {
  rascunho: 'bg-gray-100 text-gray-700',
  confirmado: 'bg-blue-100 text-blue-700',
  faturado: 'bg-green-100 text-green-700',
  cancelado: 'bg-red-100 text-red-700',
}

export default function PedidosPage() {
  const [pedidos, setPedidos] = useState<Pedido[]>([])
  const [loading, setLoading] = useState(true)
  const [status, setStatus] = useState('')
  const router = useRouter()

  useEffect(() => {
    fetchPedidos()
  }, [status])

  const fetchPedidos = async () => {
    setLoading(true)
    try {
      const params: Record<string, string> = {}
      if (status) params['filter[status]'] = status

      const response = await api.get('/api/pedidos', { params })
      setPedidos(response.data.data || response.data)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-xl font-bold text-gray-900">Pedidos</h2>
        <button
          onClick={() => router.push('/pedidos/novo')}
          className="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg px-4 py-2 transition"
        >
          Novo Pedido
        </button>
      </div>

      {/* Filtros */}
      <div className="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <div className="flex gap-4">
          <select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="rascunho">Rascunho</option>
            <option value="confirmado">Confirmado</option>
            <option value="faturado">Faturado</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>
      </div>

      {/* Tabela */}
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="text-left px-4 py-3 text-gray-600 font-medium">#</th>
              <th className="text-left px-4 py-3 text-gray-600 font-medium">Cliente</th>
              <th className="text-left px-4 py-3 text-gray-600 font-medium">Data</th>
              <th className="text-left px-4 py-3 text-gray-600 font-medium">Status</th>
              <th className="text-right px-4 py-3 text-gray-600 font-medium">Total</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr>
                <td colSpan={5} className="text-center py-8 text-gray-400">
                  Carregando...
                </td>
              </tr>
            ) : pedidos.length === 0 ? (
              <tr>
                <td colSpan={5} className="text-center py-8 text-gray-400">
                  Nenhum pedido encontrado.
                </td>
              </tr>
            ) : (
              pedidos.map((pedido) => (
                <tr
                  key={pedido.id}
                  onClick={() => router.push(`/pedidos/${pedido.id}`)}
                  className="border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition"
                >
                  <td className="px-4 py-3 text-gray-500">#{pedido.id}</td>
                  <td className="px-4 py-3 font-medium text-gray-900">
                    {pedido.cliente?.nome}
                  </td>
                  <td className="px-4 py-3 text-gray-600">
                    {new Date(pedido.data_emissao).toLocaleDateString('pt-BR')}
                  </td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${statusColors[pedido.status]}`}>
                      {pedido.status}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-right font-medium text-gray-900">
                    {Number(pedido.total_geral).toLocaleString('pt-BR', {
                      style: 'currency',
                      currency: 'BRL',
                    })}
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
