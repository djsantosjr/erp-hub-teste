'use client'

import { useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { useAuth } from '@/hooks/useAuth'

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const { user, loading, logout, switchEmpresa } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!loading && !user) {
      router.push('/login')
    }
  }, [user, loading, router])

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p className="text-gray-500">Carregando...</p>
      </div>
    )
  }

  if (!user) return null

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white border-b border-gray-200 px-6 py-3">
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <div className="flex items-center gap-4">
            <h1 className="text-lg font-bold text-gray-900">ERP Hub</h1>
            <nav className="flex gap-4">
            <a href="/pedidos" className="text-sm text-gray-600 hover:text-blue-600 transition"></a>
            </nav>
          </div>

          <div className="flex items-center gap-4">
            {/* Empresa Switcher */}
            {user.empresas && user.empresas.length > 1 && (
              <select
                defaultValue={user.idempresa_default}
                onChange={(e) => switchEmpresa(Number(e.target.value))}
                className="text-sm border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                {user.empresas.map((empresa) => (
                  <option key={empresa.id} value={empresa.id}>
                    {empresa.nome}
                  </option>
                ))}
              </select>
            )}

            {user.empresas && user.empresas.length === 1 && (
              <span className="text-sm text-gray-600">
                {user.empresas[0]?.nome}
              </span>
            )}

            {/* Usuario */}
            <div className="flex items-center gap-2">
              <span className="text-sm text-gray-700">{user.name}</span>
              <button
                onClick={logout}
                className="text-sm text-red-600 hover:text-red-700 transition"
              >
                Sair
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Conteúdo */}
      <main className="max-w-7xl mx-auto px-6 py-6">
        {children}
      </main>
    </div>
  )
}
