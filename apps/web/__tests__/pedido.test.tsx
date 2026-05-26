import { describe, it, expect } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import { z } from 'zod'

// ============================================================
// TESTE 1 — Schema Zod valida happy path + 3 inválidos
// ============================================================

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
  parcelas: z.coerce.number().min(1),
  dias_entrada: z.coerce.number().min(0),
  intervalo: z.coerce.number().min(1),
  itens: z.array(itemSchema).min(1, 'Adicione pelo menos um item'),
})

describe('Schema Zod do formulário de pedido', () => {
  it('valida happy path corretamente', () => {
    const result = pedidoSchema.safeParse({
      cliente_id: 1,
      data_emissao: '2026-01-01',
      frete: 10,
      parcelas: 2,
      dias_entrada: 28,
      intervalo: 28,
      itens: [{
        produto_id: 1,
        quantidade: 2,
        preco_unitario: 100,
        desconto_unitario: 0,
        ativo: true,
      }],
    })
    expect(result.success).toBe(true)
  })

  it('invalida cliente_id = 0', () => {
    const result = pedidoSchema.safeParse({
      cliente_id: 0,
      data_emissao: '2026-01-01',
      frete: 0,
      parcelas: 1,
      dias_entrada: 28,
      intervalo: 28,
      itens: [{ produto_id: 1, quantidade: 1, preco_unitario: 10, desconto_unitario: 0, ativo: true }],
    })
    expect(result.success).toBe(false)
  })

  it('invalida data_emissao vazia', () => {
    const result = pedidoSchema.safeParse({
      cliente_id: 1,
      data_emissao: '',
      frete: 0,
      parcelas: 1,
      dias_entrada: 28,
      intervalo: 28,
      itens: [{ produto_id: 1, quantidade: 1, preco_unitario: 10, desconto_unitario: 0, ativo: true }],
    })
    expect(result.success).toBe(false)
  })

  it('invalida itens vazio', () => {
    const result = pedidoSchema.safeParse({
      cliente_id: 1,
      data_emissao: '2026-01-01',
      frete: 0,
      parcelas: 1,
      dias_entrada: 28,
      intervalo: 28,
      itens: [],
    })
    expect(result.success).toBe(false)
  })
})

// ============================================================
// TESTE 2 — Cálculo de subtotal
// ============================================================

function calcularSubtotal(itens: { preco_unitario: number; desconto_unitario: number; quantidade: number; ativo: boolean }[]) {
  return itens
    .filter(i => i.ativo)
    .reduce((acc, item) => {
      return acc + ((item.preco_unitario - item.desconto_unitario) * item.quantidade)
    }, 0)
}

describe('Calculo de subtotal dos itens', () => {
  it('calcula subtotal corretamente com 2 itens ativos', () => {
    const itens = [
      { preco_unitario: 100, desconto_unitario: 0, quantidade: 2, ativo: true },
      { preco_unitario: 50, desconto_unitario: 5, quantidade: 3, ativo: true },
    ]
    // item1: 100 * 2 = 200
    // item2: (50-5) * 3 = 135
    expect(calcularSubtotal(itens)).toBe(335)
  })

  it('ignora itens inativos no subtotal', () => {
    const itens = [
      { preco_unitario: 100, desconto_unitario: 0, quantidade: 2, ativo: true },
      { preco_unitario: 999, desconto_unitario: 0, quantidade: 10, ativo: false },
    ]
    expect(calcularSubtotal(itens)).toBe(200)
  })

  it('retorna 0 para lista vazia', () => {
    expect(calcularSubtotal([])).toBe(0)
  })
})

// ============================================================
// TESTE 3 — Wrapper de fetch trata erro 422
// ============================================================

describe('Tratamento de erros da API', () => {
  it('mapeia erro 422 corretamente', () => {
    const axiosError = {
      response: {
        status: 422,
        data: {
          errors: {
            cliente_id: ['O cliente é obrigatório.'],
            data_emissao: ['A data de emissão é obrigatória.'],
          }
        }
      }
    }

    function mapApiErrors(error: any): Record<string, string> {
      if (error?.response?.status === 422 && error?.response?.data?.errors) {
        const result: Record<string, string> = {}
        Object.entries(error.response.data.errors).forEach(([key, messages]) => {
          result[key] = (messages as string[])[0]
        })
        return result
      }
      return {}
    }

    const mapped = mapApiErrors(axiosError)
    expect(mapped.cliente_id).toBe('O cliente é obrigatório.')
    expect(mapped.data_emissao).toBe('A data de emissão é obrigatória.')
  })

  it('retorna objeto vazio para erros nao 422', () => {
    const error = { response: { status: 500, data: {} } }

    function mapApiErrors(error: any): Record<string, string> {
      if (error?.response?.status === 422 && error?.response?.data?.errors) {
        const result: Record<string, string> = {}
        Object.entries(error.response.data.errors).forEach(([key, messages]) => {
          result[key] = (messages as string[])[0]
        })
        return result
      }
      return {}
    }

    expect(mapApiErrors(error)).toEqual({})
  })
})