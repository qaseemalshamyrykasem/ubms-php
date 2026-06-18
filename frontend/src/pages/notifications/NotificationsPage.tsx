import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { Bell, CheckCheck, Trash2, Info, AlertTriangle, CheckCircle, AlertCircle } from 'lucide-react'
import { notificationApi } from '@/lib/api-services'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { timeAgo } from '@/lib/utils'

const ICONS: Record<string, any> = { info: Info, success: CheckCircle, warning: AlertTriangle, danger: AlertCircle }
const COLORS: Record<string, string> = { info: 'text-blue-500', success: 'text-emerald-500', warning: 'text-amber-500', danger: 'text-red-500' }

export default function NotificationsPage() {
  const { t } = useTranslation()
  const qc = useQueryClient()
  const { data, isLoading } = useQuery({
    queryKey: ['notifications'],
    queryFn: () => notificationApi.list({ per_page: 50 }).then(r => r.data),
  })

  const markRead = useMutation({
    mutationFn: (id: number) => notificationApi.markRead(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['notifications'] }),
  })
  const markAll = useMutation({
    mutationFn: () => notificationApi.markAllRead(),
    onSuccess: () => { toast.success(t('notifications.all_marked_read') || 'تم'); qc.invalidateQueries({ queryKey: ['notifications'] }) },
  })
  const clearAll = useMutation({
    mutationFn: () => notificationApi.clearAll(),
    onSuccess: () => { toast.success(t('notifications.cleared') || 'تم المسح'); qc.invalidateQueries({ queryKey: ['notifications'] }) },
  })
  const del = useMutation({
    mutationFn: (id: number) => notificationApi.delete(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['notifications'] }),
  })

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{t('notifications.title')}</h1>
          <p className="text-muted-foreground">{data?.data?.length || 0} {t('common.results')}</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => markAll.mutate()}><CheckCheck className="h-4 w-4" /> {t('notifications.markAllRead')}</Button>
          <Button variant="outline" onClick={() => clearAll.mutate()}><Trash2 className="h-4 w-4" /> {t('notifications.clearAll')}</Button>
        </div>
      </div>

      <div className="space-y-2">
        {isLoading ? (
          Array.from({ length: 5 }).map((_, i) => <Card key={i}><CardContent className="p-4"><div className="skeleton h-16 rounded" /></CardContent></Card>)
        ) : data?.data?.length === 0 ? (
          <Card><CardContent className="p-12 text-center text-muted-foreground">
            <Bell className="h-12 w-12 mx-auto mb-3 opacity-50" />
            {t('notifications.empty')}
          </CardContent></Card>
        ) : data?.data?.map((n: any) => {
          const Icon = ICONS[n.type] || Bell
          return (
            <Card key={n.id} className={`card-hover ${!n.is_read ? 'border-primary/30 bg-primary/5' : ''}`}>
              <CardContent className="p-4 flex items-start gap-3">
                <Icon className={`h-5 w-5 mt-0.5 ${COLORS[n.type]}`} />
                <div className="flex-1 min-w-0">
                  <p className={`font-medium ${n.is_read ? '' : 'font-bold'}`}>{n.title}</p>
                  <p className="text-sm text-muted-foreground line-clamp-2">{n.body}</p>
                  <p className="text-xs text-muted-foreground mt-1">{timeAgo(n.created_at)}</p>
                </div>
                <div className="flex gap-1">
                  {!n.is_read && <Button variant="ghost" size="sm" onClick={() => markRead.mutate(n.id)}>✓</Button>}
                  <Button variant="ghost" size="sm" onClick={() => del.mutate(n.id)}><Trash2 className="h-3 w-3" /></Button>
                </div>
              </CardContent>
            </Card>
          )
        })}
      </div>
    </div>
  )
}
