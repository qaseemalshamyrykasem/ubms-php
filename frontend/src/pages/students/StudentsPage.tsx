import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { useAuthStore } from '@/store/auth'
import { structureApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { initials } from '@/lib/utils'

export default function StudentsPage() {
  const { t } = useTranslation()
  const { user } = useAuthStore()
  const batchId = user?.representative?.batch?.id
  const { data, isLoading } = useQuery({
    queryKey: ['batch-students', batchId],
    queryFn: () => structureApi.batchStudents(batchId!).then(r => r.data),
    enabled: !!batchId,
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('nav.students')}</h1>
        <p className="text-muted-foreground">{data?.data?.length || 0} طالب</p>
      </div>

      <Card>
        <CardContent className="p-0">
          {isLoading ? (
            <div className="p-6"><div className="skeleton h-64 rounded" /></div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-muted/50">
                  <tr className="text-muted-foreground">
                    <th className="text-right p-3">الطالب</th>
                    <th className="text-center p-3">الرقم الجامعي</th>
                    <th className="text-center p-3">البريد</th>
                    <th className="text-center p-3">الهاتف</th>
                    <th className="text-center p-3">تيليجرام</th>
                  </tr>
                </thead>
                <tbody>
                  {data?.data?.map((s: any) => (
                    <tr key={s.id} className="border-b hover:bg-accent/50">
                      <td className="p-3">
                        <div className="flex items-center gap-3">
                          <Avatar className="h-9 w-9"><AvatarFallback>{initials(s.name_ar || s.name)}</AvatarFallback></Avatar>
                          <div>
                            <p className="font-medium">{s.name_ar || s.name}</p>
                            <p className="text-xs text-muted-foreground">{s.name}</p>
                          </div>
                        </div>
                      </td>
                      <td className="p-3 text-center font-mono">{s.student_id}</td>
                      <td className="p-3 text-center text-muted-foreground">{s.email}</td>
                      <td className="p-3 text-center text-muted-foreground">{s.phone || '—'}</td>
                      <td className="p-3 text-center">
                        {s.telegram_connected
                          ? <Badge variant="success">متصل</Badge>
                          : <Badge variant="secondary">غير متصل</Badge>}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
