'use client'

import { useEffect, useState } from 'react'
import { useForm, useFieldArray } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useRouter } from 'next/navigation'
import api from '@/lib/api'

const itemSchema = z.object({
  produto_id: z.coerce.number().min(1, 'Selecione um produto'),
  quantidade: z.coerce.number().min(0.001, 'Quantidade inválida'),
  preco_unitario: z.coerce.number().min(0, 'Preço inválido'),
  desconto_unitario: z.coerce.number().min(0),
  ativo: z.boolean(),
})

const pedidoSchema = z.object({
  cliente_id: z.coerce.number().min(1, 'Selecione um cliente'),
  data_emissao: z.string().min(1, 'Data obrigatória'),
  frete: z.coerce.number().min(0),
  desconto_cabecalho: z.coerce.number().min(0),
  parcelas: z.coerce.number().min(1),
  dias_entrada: z.coerce.number().min(0),
  intervalo: z.coerce.number().min(1),
  itens: z.array(itemSchema).min(1, 'Adicione pelo menos um item'),
})

type PedidoForm = z.infer<typeof pedidoSchema>

export default function NovoPedidoPage() {
  const router = useRouter()
  const [clientes, setClientes] = useState<any[]>([])
  const [produtos, setProdutos] = useState<any[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const { register, control, handleSubmit, watch, formState: { errors } } = useForm<PedidoForm>({
    resolver: zodResolver(pedidoSchema),
    defaultValues: {
      frete: 0,
      desconto_cabecalho: 0,
      parcelas: 1,
      dias_entrada: 28,
      intervalo: 28,
      itens: [{ produto_id: 0, quantidade: 1, preco_unitario: 0, desconto_unitario: 0, ativo: true }],
    },
  })

  const { fields, append, remove } = useFieldArray({ control, name: 'itens' })
  const itens = watch('itens')
  const frete = watch('frete') || 0

  useEffect(() => {
    api.get('/api/clientes').then(r => setClientes(r.data.data || r.data))
    api.get('/api/produtos').then(r => setProdutos(r.data.data || r.data))
  }, [])

  const calcularTotal = (item: any) => {
    const preco = Number(item.preco_unitario) || 0
    const desconto = Number(item.desconto_unitario) || 0
    const qtd = Number(item.quantidade) || 0
    return ((preco - desconto) * qtd)
  }

  const subtotal = itens.reduce((acc, item) => acc + calcularTotal(item), 0)
  const totalGeral = subtotal + Number(frete)

  const onSubmit = async (data: PedidoForm) => {
    setLoading(true)
    setError(null)
    try {
      await api.post('/api/pedidos', data)
      router.push('/pedidos')
    } catch (err: any) {
      setError(err.response?.data?.message || 'Erro ao criar pedido.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <div className="flex items-center gap-4 mb-6">
        <button onClick={() => router.back()} className="text-gray-500 hover:text-gray-700">
          ← Voltar
        </button>
        <h2 className="text-xl font-bold text-gray-900">Novo Pedido</h2>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        {/* Cabeçalho */}
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <h3 className="font-medium text-gray-900 mb-4">Dados do Pedido</h3>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <select {...register('cliente_id')} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Selecione...</option>
                {clientes.map(c => <option key={c.id} value={c.id}>{c.nome}</option>)}
              </select>
              {errors.cliente_id && <p className="text-red-500 text-xs mt-1">{errors.cliente_id.message}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Data de Emissão</label>
              <input {...register('data_emissao')} type="date" className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
              {errors.data_emissao && <p className="text-red-500 text-xs mt-1">{errors.data_emissao.message}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Frete (R$)</label>
              <input {...register('frete')} type="number" step="0.01" className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Parcelas</label>
              <input {...register('parcelas')} type="number" min="1" className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Dias Entrada</label>
              <input {...register('dias_entrada')} type="number" className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Intervalo (dias)</label>
              <input {...register('intervalo')} type="number" className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            </div>
          </div>
        </div>

        {/* Itens */}
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-medium text-gray-900">Itens</h3>
            <button
              type="button"
              onClick={() => append({ produto_id: 0, quantidade: 1, preco_unitario: 0, desconto_unitario: 0, ativo: true })}
              className="text-sm text-blue-600 hover:text-blue-700"
            >
              + Adicionar Item
            </button>
          </div>

          <table className="w-full text-sm">
            <thead className="bg-gray-50">
              <tr>
                <th className="text-left px-3 py-2 text-gray-600">Produto</th>
                <th className="text-left px-3 py-2 text-gray-600">Qtd</th>
                <th className="text-left px-3 py-2 text-gray-600">Preço Unit.</th>
                <th className="text-left px-3 py-2 text-gray-600">Desconto</th>
                <th className="text-right px-3 py-2 text-gray-600">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {fields.map((field, index) => (
                <tr key={field.id} className="border-t border-gray-100">
                  <td className="px-3 py-2">
                    <select {...register(`itens.${index}.produto_id`)} className="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                      <option value="">Selecione...</option>
                      {produtos.map(p => <option key={p.id} value={p.id}>{p.descricao}</option>)}
                    </select>
                  </td>
                  <td className="px-3 py-2">
                    <input {...register(`itens.${index}.quantidade`)} type="number" step="0.001" className="w-20 border border-gray-300 rounded px-2 py-1 text-sm" />
                  </td>
                  <td className="px-3 py-2">
                    <input {...register(`itens.${index}.preco_unitario`)} type="number" step="0.01" className="w-24 border border-gray-300 rounded px-2 py-1 text-sm" />
                  </td>
                  <td className="px-3 py-2">
                    <input {...register(`itens.${index}.desconto_unitario`)} type="number" step="0.01" className="w-24 border border-gray-300 rounded px-2 py-1 text-sm" />
                  </td>
                  <td className="px-3 py-2 text-right font-medium">
                    {calcularTotal(itens[index]).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                  </td>
                  <td className="px-3 py-2">
                    {fields.length > 1 && (
                      <button type="button" onClick={() => remove(index)} className="text-red-500 hover:text-red-700 text-xs">
                        Remover
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Totais */}
        <div className="bg-white rounded-xl border border-gray-200 p-6">
          <div className="flex justify-end gap-8 text-sm">
            <div className="text-right">
              <p className="text-gray-500">Subtotal</p>
              <p className="font-medium">{subtotal.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</p>
            </div>
            <div className="text-right">
              <p className="text-gray-500">Frete</p>
              <p className="font-medium">{Number(frete).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</p>
            </div>
            <div className="text-right">
              <p className="text-gray-500 font-medium">Total Geral</p>
              <p className="text-lg font-bold text-blue-600">{totalGeral.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</p>
            </div>
          </div>
        </div>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3">
            {error}
          </div>
        )}

        <div className="flex justify-end gap-3">
          <button type="button" onClick={() => router.back()} className="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
            Cancelar
          </button>
          <button type="submit" disabled={loading} className="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50">
            {loading ? 'Salvando...' : 'Criar Pedido'}
          </button>
        </div>
      </form>
    </div>
  )
}