import axios from 'axios'

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Injeta X-Empresa-Id em todas as requisições
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const empresaId = localStorage.getItem('empresa_id')
    if (empresaId) {
      config.headers['X-Empresa-Id'] = empresaId
    }
  }
  return config
})

// Redireciona para login se 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && typeof window !== 'undefined') {
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api