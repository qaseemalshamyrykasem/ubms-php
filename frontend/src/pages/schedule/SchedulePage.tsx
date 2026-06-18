import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { scheduleApi } from '@/lib/api-services'
import { useAuthStore } from '@/store/auth'

const DAYS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as const

export default function SchedulePage() {
  const { t } = useTranslation()
  const { user } = useAuthStore()
  const { data, isLoading } = useQuery({
    queryKey: ['schedules'],
    queryFn: () => scheduleApi.list().then(r => r.data),
  })

  if (isLoading) return <div className="skeleton h-96 rounded-xl" />

  const grouped = (data?.data || {}) as Record<string, any[]>

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('nav.schedule')}</h1>
        <p className="text-muted-foreground">{t('schedule.weekly')}</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {DAYS.map(day => {
          const items = grouped[day] || []
          if (items.length === 0) return null
          return (
            <Card key={day} className="card-hover">
              <CardHeader className="pb-2"><CardTitle className="text-base flex items-center justify-between">{t(`schedule.${day}`)} <Badge variant="outline">{items.length}</Badge></CardTitle></CardHeader>
              <CardContent className="space-y-2">
                {items.map(s => (
                  <div key={s.id} className="rounded-lg border p-2 text-sm">
                    <div className="flex items-center justify-between">
                      <p className="font-medium">{s.course?.name_ar}</p>
                      <Badge variant="secondary">{s.start_time} - {s.end_time}</Badge>
                    </div>
                    <p className="text-xs text-muted-foreground mt-1">
                      {s.instructor_name} • {s.room || '—'} {s.building ? `• ${s.building}` : ''}
                    </p>
                  </div>
                ))}
              </CardContent>
            </Card>
          )
        })}
      </div>

      {Object.keys(grouped).length === 0 && (
        <Card><CardContent className="p-12 text-center text-muted-foreground">{t('common.noData')}</CardContent></Card>
      )}
    </div>
  )
}
