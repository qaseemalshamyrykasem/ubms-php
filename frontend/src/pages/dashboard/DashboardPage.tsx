import { useQuery } from '@tanstack/react-query'
import { motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { Link } from 'react-router-dom'
import { Users, Megaphone, BookOpen, CalendarCheck, TrendingUp, Bell, Award, Clock } from 'lucide-react'
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, PieChart, Pie, Cell, Legend } from 'recharts'
import { dashboardApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Skeleton } from '@/components/ui/skeleton'
import { useAuthStore } from '@/store/auth'
import { formatDate, timeAgo } from '@/lib/utils'

const TYPE_COLORS: Record<string, string> = {
  holiday: '#10b981', assignment: '#3b82f6', lecture: '#8b5cf6', schedule: '#f59e0b',
  general: '#6b7280', urgent: '#ef4444', emergency: '#dc2626', meeting: '#06b6d4', important: '#f97316',
}

export default function DashboardPage() {
  const { user } = useAuthStore()
  const { t, i18n } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard', 'stats'],
    queryFn: () => dashboardApi.stats().then(r => r.data),
  })

  if (isLoading) {
    return (
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-32 rounded-xl" />)}
      </div>
    )
  }

  const stats = data?.stats || {}
  const isRep = user?.role === 'representative'
  const isStudent = user?.role === 'student'

  const statCards = isStudent
    ? [
        { key: 'announcements', label: t('dashboard.announcementCount'), icon: Megaphone, color: 'from-blue-500 to-indigo-500' },
        { key: 'assignments', label: t('dashboard.assignmentCount'), icon: BookOpen, color: 'from-purple-500 to-pink-500' },
        { key: 'attendance_rate', label: t('dashboard.attendanceRate'), icon: Award, color: 'from-emerald-500 to-teal-500', suffix: '%' },
        { key: 'unread_notifications', label: t('dashboard.unreadNotifications'), icon: Bell, color: 'from-amber-500 to-orange-500' },
      ]
    : [
        { key: 'students', label: t('dashboard.studentCount'), icon: Users, color: 'from-blue-500 to-indigo-500' },
        { key: 'announcements', label: t('dashboard.announcementCount'), icon: Megaphone, color: 'from-purple-500 to-pink-500' },
        { key: 'assignments', label: t('dashboard.assignmentCount'), icon: BookOpen, color: 'from-emerald-500 to-teal-500' },
        { key: 'attendance_rate', label: t('dashboard.attendanceRate'), icon: TrendingUp, color: 'from-amber-500 to-orange-500', suffix: '%' },
      ]

  const dailyActivity = data?.daily_activity ? Object.entries(data.daily_activity).map(([date, count]) => ({ date: date.slice(5), count: count as number })) : []
  const byType = data?.announcements_by_type ? Object.entries(data.announcements_by_type).map(([type, count]) => ({ name: t(`announcements.types.${type}`), value: count as number, color: TYPE_COLORS[type] || '#999' })) : []

  return (
    <div className="space-y-6">
      {/* Header */}
      <motion.div initial={{ opacity: 0, y: -10 }} animate={{ opacity: 1, y: 0 }} className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{t('dashboard.welcome')}, {user?.name_ar || user?.name} 👋</h1>
          <p className="text-muted-foreground mt-1">{data?.batch?.chain || 'لوحة التحكم'}</p>
        </div>
        <Badge variant="secondary" className="capitalize">{t(`nav.${user?.role}`)}</Badge>
      </motion.div>

      {/* Stat cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {statCards.map((card, i) => (
          <motion.div key={card.key} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.05 }}>
            <Card className="card-hover overflow-hidden relative">
              <CardContent className="p-5">
                <div className={`absolute top-0 left-0 h-full w-1 bg-gradient-to-b ${card.color}`} />
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm text-muted-foreground">{card.label}</p>
                    <p className="text-2xl font-bold mt-1">{stats[card.key] || 0}{card.suffix || ''}</p>
                  </div>
                  <div className={`flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${card.color} text-white shadow-lg`}>
                    <card.icon className="h-6 w-6" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        ))}
      </div>

      {/* Charts row */}
      <div className="grid gap-6 lg:grid-cols-2">
        {dailyActivity.length > 0 && (
          <Card>
            <CardHeader><CardTitle>{t('dashboard.dailyActivity')}</CardTitle></CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={280}>
                <BarChart data={dailyActivity}>
                  <XAxis dataKey="date" tick={{ fontSize: 11 }} />
                  <YAxis tick={{ fontSize: 11 }} />
                  <Tooltip contentStyle={{ borderRadius: 12, border: '1px solid #e5e7eb' }} />
                  <Bar dataKey="count" fill="#6366f1" radius={[8, 8, 0, 0]} name={t('dashboard.dailyActivity')} />
                </BarChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        )}

        {byType.length > 0 && (
          <Card>
            <CardHeader><CardTitle>{t('dashboard.announcementsByType')}</CardTitle></CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={280}>
                <PieChart>
                  <Pie data={byType} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={90} label>
                    {byType.map((entry, i) => <Cell key={i} fill={entry.color} />)}
                  </Pie>
                  <Tooltip />
                  <Legend />
                </PieChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        )}
      </div>

      {/* Student attendance stats */}
      {isStudent && data?.attendance_stats && (
        <Card>
          <CardHeader><CardTitle>{t('dashboard.myAttendance')}</CardTitle></CardHeader>
          <CardContent>
            <div className="grid gap-6 md:grid-cols-4">
              <AttendanceBox label={t('dashboard.present')} value={data.attendance_stats.present} color="text-emerald-600" />
              <AttendanceBox label={t('dashboard.late')} value={data.attendance_stats.late} color="text-amber-600" />
              <AttendanceBox label={t('dashboard.absent')} value={data.attendance_stats.absent} color="text-red-600" />
              <AttendanceBox label={t('dashboard.attendanceRate')} value={`${data.attendance_stats.rate}%`} color="text-indigo-600" />
            </div>
            <Progress value={data.attendance_stats.rate} className="mt-4" indicatorClassName="bg-gradient-to-r from-emerald-500 to-teal-500" />
          </CardContent>
        </Card>
      )}

      {/* Recent announcements */}
      {data?.recent_announcements && data.recent_announcements.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center justify-between">
              <span>{t('dashboard.recentAnnouncements')}</span>
              <Link to="/announcements" className="text-sm text-primary hover:underline">{t('common.all')}</Link>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {data.recent_announcements.slice(0, 5).map((a) => (
              <Link key={a.id} to="/announcements" className="flex items-center gap-3 rounded-lg p-2 hover:bg-accent transition">
                <div className="h-2 w-2 rounded-full" style={{ background: TYPE_COLORS[a.type] || '#999' }} />
                <div className="flex-1 min-w-0">
                  <p className="font-medium text-sm truncate">{a.title}</p>
                  <p className="text-xs text-muted-foreground">{timeAgo(a.published_at || a.created_at)}</p>
                </div>
                <Badge variant="outline">{t(`announcements.types.${a.type}`)}</Badge>
              </Link>
            ))}
          </CardContent>
        </Card>
      )}

      {/* Upcoming assignments */}
      {data?.upcoming_assignments && data.upcoming_assignments.length > 0 && (
        <Card>
          <CardHeader><CardTitle>{t('dashboard.upcomingAssignments')}</CardTitle></CardHeader>
          <CardContent className="space-y-2">
            {data.upcoming_assignments.slice(0, 5).map((a) => (
              <Link key={a.id} to="/assignments" className="flex items-center gap-3 rounded-lg p-2 hover:bg-accent transition">
                <Clock className="h-4 w-4 text-amber-500" />
                <div className="flex-1 min-w-0">
                  <p className="font-medium text-sm truncate">{a.title}</p>
                  <p className="text-xs text-muted-foreground">{formatDate(a.deadline, i18n.language)}</p>
                </div>
              </Link>
            ))}
          </CardContent>
        </Card>
      )}
    </div>
  )
}

function AttendanceBox({ label, value, color }: { label: string; value: number | string; color: string }) {
  return (
    <div className="text-center">
      <p className={`text-3xl font-bold ${color}`}>{value}</p>
      <p className="text-xs text-muted-foreground mt-1">{label}</p>
    </div>
  )
}
