import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { Plus, GraduationCap, Users } from 'lucide-react'
import { structureApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

export default function BatchesPage() {
  const { t } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['batches', 'admin-list'],
    queryFn: () => structureApi.batches({ per_page: 50 }).then(r => r.data),
  })

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{t('nav.batches')}</h1>
          <p className="text-muted-foreground">{data?.data?.length || 0} دفعة</p>
        </div>
        <Button><Plus className="h-4 w-4" /> دفعة جديدة</Button>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {isLoading ? (
          Array.from({ length: 3 }).map((_, i) => <Card key={i}><CardContent className="p-6"><div className="skeleton h-32 rounded" /></CardContent></Card>)
        ) : data?.data?.map((b: any) => (
          <Card key={b.id} className="card-hover">
            <CardContent className="p-5">
              <div className="flex items-start gap-3">
                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white">
                  <GraduationCap className="h-6 w-6" />
                </div>
                <div className="flex-1">
                  <div className="flex items-center gap-2">
                    <Badge variant="secondary">{b.code}</Badge>
                    {b.is_active && <Badge variant="success">فعّالة</Badge>}
                  </div>
                  <h3 className="font-bold mt-1">{b.name_ar}</h3>
                  <p className="text-xs text-muted-foreground mt-1">{b.section?.level?.department?.college?.name_ar || ''}</p>
                  <p className="text-xs text-muted-foreground">{b.start_year}{b.end_year ? ` - ${b.end_year}` : ''}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}
