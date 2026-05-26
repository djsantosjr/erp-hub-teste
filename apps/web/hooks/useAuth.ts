'use client'

import { useState, useEffect, useCallback } from 'react'
import { useRouter, usePathname } from 'next/navigation'
import { getUser, login as authLogin, logout as authLogout, User } from '@/lib/auth'

export function useAuth() {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  const router = useRouter()
  const pathname = usePathname()

  useEffect(() => {
    // Não busca usuário na página de login
    if (pathname === '/login') {
      setLoading(false)
      return
    }

    getUser()
      .then(setUser)
      .catch(() => setUser(null))
      .finally(() => setLoading(false))
  }, [pathname])

  const login = useCallback(async (email: string, password: string) => {
    const user = await authLogin(email, password)
    setUser(user)
    if (user.idempresa_default) {
      localStorage.setItem('empresa_id', String(user.idempresa_default))
    }
    router.push('/pedidos')
    return user
  }, [router])

  const logout = useCallback(async () => {
    await authLogout()
    setUser(null)
    localStorage.removeItem('empresa_id')
    router.push('/login')
  }, [router])

  const switchEmpresa = useCallback((empresaId: number) => {
    localStorage.setItem('empresa_id', String(empresaId))
    window.location.reload()
  }, [])

  return { user, loading, login, logout, switchEmpresa }
}