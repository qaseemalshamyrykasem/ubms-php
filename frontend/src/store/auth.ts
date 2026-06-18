import { create } from 'zustand'
import { persist } from 'zustand/middleware'
import type { User } from '@/types'

interface AuthState {
  token: string | null
  user: User | null
  isAuthenticated: boolean
  setAuth: (token: string, user: User) => void
  updateUser: (user: User) => void
  logout: () => void
  hasRole: (role: string | string[]) => boolean
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      token: null,
      user: null,
      isAuthenticated: false,
      setAuth: (token, user) => {
        localStorage.setItem('ubms_token', token)
        localStorage.setItem('ubms_user', JSON.stringify(user))
        set({ token, user, isAuthenticated: true })
      },
      updateUser: (user) => {
        localStorage.setItem('ubms_user', JSON.stringify(user))
        set({ user })
      },
      logout: () => {
        localStorage.removeItem('ubms_token')
        localStorage.removeItem('ubms_user')
        set({ token: null, user: null, isAuthenticated: false })
      },
      hasRole: (role) => {
        const r = get().user?.role
        if (!r) return false
        if (Array.isArray(role)) return role.includes(r)
        return r === role
      },
    }),
    { name: 'ubms-auth' },
  ),
)
