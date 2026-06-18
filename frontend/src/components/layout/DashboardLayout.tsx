import { useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import {
  LayoutDashboard, Megaphone, CalendarCheck, BookOpen, Calendar, Bell,
  Send, FileBarChart, Users, GraduationCap, ChevronLeft, Menu, LogOut,
  Settings, User as UserIcon, X, Search,
} from 'lucide-react'
import { cn, initials } from '@/lib/utils'
import { useAuthStore } from '@/store/auth'
import { useUIStore } from '@/store/ui'
import { Button } from '@/components/ui/button'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { notificationApi } from '@/lib/api-services'
import { useQuery } from '@tanstack/react-query'

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const [mobileOpen, setMobileOpen] = useState(false)
  const { sidebarCollapsed, toggleSidebar } = useUIStore()
  const { user, logout } = useAuthStore()
  const { t } = useTranslation()
  const location = useLocation()
  const navigate = useNavigate()

  const { data: unreadData } = useQuery({
    queryKey: ['notifications', 'unread-count'],
    queryFn: () => notificationApi.unreadCount().then(r => r.data),
    refetchInterval: 30_000,
  })

  const nav = buildNavForRole(user?.role, t, unreadData?.count || 0)

  const handleLogout = () => {
    logout()
    navigate('/auth/login')
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/30">
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => setMobileOpen(false)}
            className="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
          />
        )}
      </AnimatePresence>

      <aside
        className={cn(
          'fixed top-0 z-50 h-screen border-l border-border bg-card/80 backdrop-blur-xl transition-all duration-300',
          sidebarCollapsed ? 'w-20' : 'w-72',
          'lg:translate-x-0',
          mobileOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0',
          'right-0',
        )}
      >
        <div className="flex h-full flex-col">
          <div className="flex h-16 items-center justify-between border-b border-border px-4">
            <Link to="/dashboard" className="flex items-center gap-2 overflow-hidden">
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-md">
                <GraduationCap className="h-5 w-5" />
              </div>
              {!sidebarCollapsed && (
                <div className="flex flex-col">
                  <span className="text-base font-bold leading-tight">UBMS</span>
                  <span className="text-[10px] text-muted-foreground">{t('common.appName')}</span>
                </div>
              )}
            </Link>
            <Button variant="ghost" size="icon" className="hidden lg:flex" onClick={toggleSidebar}>
              <ChevronLeft className={cn('h-4 w-4 transition-transform', sidebarCollapsed && 'rotate-180')} />
            </Button>
            <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setMobileOpen(false)}>
              <X className="h-4 w-4" />
            </Button>
          </div>

          <nav className="flex-1 overflow-y-auto scrollbar-thin py-4 px-3">
            <ul className="space-y-1">
              {nav.map((item) => {
                const active = location.pathname === item.path || location.pathname.startsWith(item.path + '/')
                return (
                  <li key={item.path}>
                    <Link
                      to={item.path}
                      onClick={() => setMobileOpen(false)}
                      className={cn(
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all',
                        active
                          ? 'bg-gradient-to-r from-indigo-600/10 to-purple-600/10 text-primary shadow-sm'
                          : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                        sidebarCollapsed && 'justify-center',
                      )}
                      title={sidebarCollapsed ? item.label : undefined}
                    >
                      <span className="relative">
                        <item.icon className={cn('h-5 w-5', active && 'text-primary')} />
                        {item.badge ? (
                          <span className="absolute -top-2 -right-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-bold text-destructive-foreground">
                            {item.badge > 99 ? '99+' : item.badge}
                          </span>
                        ) : null}
                      </span>
                      {!sidebarCollapsed && <span className="flex-1">{item.label}</span>}
                      {!sidebarCollapsed && active && (
                        <motion.div layoutId="active-pill" className="h-1.5 w-1.5 rounded-full bg-primary" />
                      )}
                    </Link>
                  </li>
                )
              })}
            </ul>
          </nav>

          <div className="border-t border-border p-3">
            <div className={cn('flex items-center gap-3 rounded-xl p-2', sidebarCollapsed && 'justify-center')}>
              <Avatar className="h-9 w-9 ring-2 ring-border">
                <AvatarImage src={user?.avatar} />
                <AvatarFallback>{initials(user?.name_ar || user?.name)}</AvatarFallback>
              </Avatar>
              {!sidebarCollapsed && (
                <div className="flex-1 overflow-hidden">
                  <p className="truncate text-sm font-medium">{user?.name_ar || user?.name}</p>
                  <p className="truncate text-xs text-muted-foreground">{user?.email}</p>
                </div>
              )}
              {!sidebarCollapsed && (
                <Button variant="ghost" size="icon" onClick={handleLogout} title={t('common.logout')}>
                  <LogOut className="h-4 w-4" />
                </Button>
              )}
            </div>
          </div>
        </div>
      </aside>

      <div className={cn('transition-all duration-300', sidebarCollapsed ? 'lg:mr-20' : 'lg:mr-72')}>
        <header className="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-background/80 px-4 backdrop-blur-xl lg:px-6">
          <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setMobileOpen(true)}>
            <Menu className="h-5 w-5" />
          </Button>

          <div className="flex flex-1 items-center gap-3">
            <div className="relative hidden md:block">
              <Search className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <input
                type="search"
                placeholder={t('common.search')}
                className="h-10 w-72 rounded-lg border border-input bg-background pr-10 pl-4 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-ring"
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    const q = (e.target as HTMLInputElement).value
                    if (q.trim()) navigate(`/search?q=${encodeURIComponent(q)}`)
                  }
                }}
              />
            </div>
          </div>

          <div className="flex items-center gap-2">
            <ThemeToggle />
            <LanguageToggle />
            <Link to="/notifications">
              <Button variant="ghost" size="icon" className="relative">
                <Bell className="h-5 w-5" />
                {unreadData && unreadData.count > 0 && (
                  <span className="absolute top-1.5 right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-bold text-destructive-foreground">
                    {unreadData.count > 99 ? '99+' : unreadData.count}
                  </span>
                )}
              </Button>
            </Link>
            <Link to="/profile">
              <Avatar className="h-9 w-9 ring-2 ring-border hover:ring-primary/50 transition">
                <AvatarImage src={user?.avatar} />
                <AvatarFallback>{initials(user?.name_ar || user?.name)}</AvatarFallback>
              </Avatar>
            </Link>
          </div>
        </header>

        <main className="p-4 lg:p-6 animate-fade-in">{children}</main>
      </div>
    </div>
  )
}

function ThemeToggle() {
  const { theme, toggleTheme } = useUIStore()
  return (
    <Button variant="ghost" size="icon" onClick={toggleTheme} title={theme === 'dark' ? 'Light' : 'Dark'}>
      {theme === 'dark' ? '🌞' : '🌙'}
    </Button>
  )
}

function LanguageToggle() {
  const { locale, setLocale } = useUIStore()
  const { i18n } = useTranslation()
  const toggle = () => {
    const next = locale === 'ar' ? 'en' : 'ar'
    setLocale(next)
    i18n.changeLanguage(next)
  }
  return (
    <Button variant="ghost" size="icon" onClick={toggle} title="Language">
      <span className="text-xs font-bold">{locale === 'ar' ? 'EN' : 'ع'}</span>
    </Button>
  )
}

interface NavItem {
  path: string
  label: string
  icon: React.ComponentType<{ className?: string }>
  badge?: number
}

function buildNavForRole(role: string | undefined, t: (k: string) => string, unread: number): NavItem[] {
  const base: Record<string, NavItem[]> = {
    student: [
      { path: '/dashboard', label: t('nav.dashboard'), icon: LayoutDashboard },
      { path: '/announcements', label: t('nav.announcements'), icon: Megaphone },
      { path: '/attendance', label: t('nav.attendance'), icon: CalendarCheck },
      { path: '/assignments', label: t('nav.assignments'), icon: BookOpen },
      { path: '/schedule', label: t('nav.schedule'), icon: Calendar },
      { path: '/notifications', label: t('nav.notifications'), icon: Bell, badge: unread },
      { path: '/telegram', label: t('nav.telegram'), icon: Send },
      { path: '/profile', label: t('common.profile'), icon: UserIcon },
    ],
    representative: [
      { path: '/dashboard', label: t('nav.dashboard'), icon: LayoutDashboard },
      { path: '/announcements', label: t('nav.announcements'), icon: Megaphone },
      { path: '/attendance', label: t('nav.attendance'), icon: CalendarCheck },
      { path: '/assignments', label: t('nav.assignments'), icon: BookOpen },
      { path: '/courses', label: t('nav.courses'), icon: BookOpen },
      { path: '/schedule', label: t('nav.schedule'), icon: Calendar },
      { path: '/reports', label: t('nav.reports'), icon: FileBarChart },
      { path: '/students', label: t('nav.students'), icon: Users },
      { path: '/notifications', label: t('nav.notifications'), icon: Bell, badge: unread },
      { path: '/telegram', label: t('nav.telegram'), icon: Send },
      { path: '/profile', label: t('common.profile'), icon: UserIcon },
    ],
    college_admin: [
      { path: '/dashboard', label: t('nav.dashboard'), icon: LayoutDashboard },
      { path: '/batches', label: t('nav.batches'), icon: GraduationCap },
      { path: '/students', label: t('nav.students'), icon: Users },
      { path: '/reports', label: t('nav.reports'), icon: FileBarChart },
      { path: '/structure', label: t('nav.structure'), icon: Settings },
      { path: '/notifications', label: t('nav.notifications'), icon: Bell, badge: unread },
      { path: '/profile', label: t('common.profile'), icon: UserIcon },
    ],
    super_admin: [
      { path: '/dashboard', label: t('nav.dashboard'), icon: LayoutDashboard },
      { path: '/batches', label: t('nav.batches'), icon: GraduationCap },
      { path: '/students', label: t('nav.students'), icon: Users },
      { path: '/reports', label: t('nav.reports'), icon: FileBarChart },
      { path: '/structure', label: t('nav.structure'), icon: Settings },
      { path: '/notifications', label: t('nav.notifications'), icon: Bell, badge: unread },
      { path: '/profile', label: t('common.profile'), icon: UserIcon },
    ],
  }
  return base[role || 'student'] || base.student
}
