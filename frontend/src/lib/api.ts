import axios, { AxiosError, type InternalAxiosRequestConfig } from 'axios'

const baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

const api = axios.create({
  baseURL,
  withCredentials: false,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  timeout: 30_000,
})

api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const token = localStorage.getItem('ubms_token')
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`
  }
  const locale = localStorage.getItem('ubms_locale') || 'ar'
  if (config.headers) config.headers['Accept-Language'] = locale
  return config
})

api.interceptors.response.use(
  (resp) => resp,
  (err: AxiosError) => {
    if (err.response?.status === 401) {
      const currentPath = window.location.pathname
      if (!currentPath.startsWith('/auth')) {
        localStorage.removeItem('ubms_token')
        localStorage.removeItem('ubms_user')
        window.location.href = '/auth/login'
      }
    }
    return Promise.reject(err)
  },
)

export default api
