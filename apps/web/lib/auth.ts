import axios from 'axios'
import api from './api'

export interface User {
  id: number
  name: string
  email: string
  idempresa_default: number
  empresas: Empresa[]
}

export interface Empresa {
  id: number
  nome: string
  cnpj: string
}

export async function getCsrfToken(): Promise<void> {
  await axios.get(
    `${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/sanctum/csrf-cookie`,
    { withCredentials: true }
  )
}

export async function login(email: string, password: string): Promise<User> {
  await getCsrfToken()
  await api.post('/api/login', { email, password })
  return getUser()
}

export async function logout(): Promise<void> {
  await api.post('/api/logout')
}

export async function getUser(): Promise<User> {
  const response = await api.get('/api/user')
  return response.data
}