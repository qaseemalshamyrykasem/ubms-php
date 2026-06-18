import { create } from 'zustand'
import { persist } from 'zustand/middleware'

type Theme = 'light' | 'dark'
type Locale = 'ar' | 'en'
type Dir = 'rtl' | 'ltr'

interface UIState {
  theme: Theme
  locale: Locale
  dir: Dir
  sidebarCollapsed: boolean
  setTheme: (t: Theme) => void
  toggleTheme: () => void
  setLocale: (l: Locale) => void
  toggleSidebar: () => void
  setSidebar: (collapsed: boolean) => void
}

const applyTheme = (theme: Theme) => {
  const root = document.documentElement
  if (theme === 'dark') root.classList.add('dark')
  else root.classList.remove('dark')
}

const applyDir = (locale: Locale) => {
  const dir = locale === 'ar' ? 'rtl' : 'ltr'
  document.documentElement.lang = locale
  document.documentElement.dir = dir
}

export const useUIStore = create<UIState>()(
  persist(
    (set, get) => ({
      theme: 'dark',
      locale: 'ar',
      dir: 'rtl',
      sidebarCollapsed: false,
      setTheme: (theme) => {
        applyTheme(theme)
        set({ theme })
      },
      toggleTheme: () => {
        const next = get().theme === 'dark' ? 'light' : 'dark'
        applyTheme(next)
        set({ theme: next })
      },
      setLocale: (locale) => {
        applyDir(locale)
        set({ locale, dir: locale === 'ar' ? 'rtl' : 'ltr' })
      },
      toggleSidebar: () => set({ sidebarCollapsed: !get().sidebarCollapsed }),
      setSidebar: (collapsed) => set({ sidebarCollapsed: collapsed }),
    }),
    {
      name: 'ubms-ui',
      onRehydrateStorage: () => (state) => {
        if (state) {
          applyTheme(state.theme)
          applyDir(state.locale)
        }
      },
    },
  ),
)
