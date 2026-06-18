import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { BookOpen, FileText } from 'lucide-react'
import { courseApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

export default function CoursesPage() {
  const { t } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['courses'],
    queryFn: () => courseApi.list({ per_page: 100 }).then(r => r.data),
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('nav.courses')}</h1>
        <p className="text-muted-foreground">{data?.data?.length || 0} {t('common.results')}</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {isLoading ? (
          Array.from({ length: 3 }).map((_, i) => <Card key={i}><CardContent className="p-6"><div className="skeleton h-40 rounded" /></CardContent></Card>)
        ) : data?.data?.map((c: any) => (
          <Card key={c.id} className="card-hover">
            <CardContent className="p-5">
              <div className="flex items-start gap-3">
                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white">
                  <BookOpen className="h-6 w-6" />
                </div>
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2">
                    <Badge variant="secondary">{c.code}</Badge>
                    <span className="text-xs text-muted-foreground">{c.credit_hours} ساعة</span>
                  </div>
                  <h3 className="font-bold mt-1">{c.name_ar}</h3>
                  <p className="text-sm text-muted-foreground">{c.name}</p>
                  {c.instructor_name && <p className="text-xs text-muted-foreground mt-2">👨‍🏫 {c.instructor_name}</p>}
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}
