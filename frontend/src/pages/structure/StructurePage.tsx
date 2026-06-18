import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { Building2, School, Layers, Grid3x3 } from 'lucide-react'
import { structureApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

export default function StructurePage() {
  const { t } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['hierarchy'],
    queryFn: () => structureApi.hierarchy().then(r => r.data),
  })

  if (isLoading) return <div className="skeleton h-96 rounded-xl" />

  const hierarchy = data?.hierarchy || []

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('nav.structure')}</h1>
        <p className="text-muted-foreground">الهيكل التنظيمي للجامعة</p>
      </div>

      <div className="space-y-4">
        {Array.isArray(hierarchy) && hierarchy.map((uni: any) => (
          <Card key={uni.id}>
            <CardHeader><CardTitle className="flex items-center gap-2"><Building2 className="h-5 w-5 text-primary" /> {uni.name_ar} ({uni.code})</CardTitle></CardHeader>
            <CardContent className="space-y-4">
              {uni.colleges?.map((col: any) => (
                <div key={col.id} className="rounded-lg border p-4">
                  <div className="flex items-center gap-2 mb-3"><School className="h-4 w-4 text-indigo-500" /><span className="font-bold">{col.name_ar}</span><Badge variant="outline">{col.code}</Badge></div>
                  <div className="space-y-3 mr-6">
                    {col.departments?.map((dep: any) => (
                      <div key={dep.id} className="rounded border p-3">
                        <div className="flex items-center gap-2 mb-2"><Layers className="h-4 w-4 text-purple-500" /><span className="font-medium">{dep.name_ar}</span></div>
                        <div className="grid gap-2 md:grid-cols-2 lg:grid-cols-3 mr-6">
                          {dep.levels?.map((lvl: any) => (
                            <div key={lvl.id} className="rounded bg-muted/50 p-2 text-sm">
                              <p className="font-medium">{lvl.name_ar}</p>
                              {lvl.sections?.map((sec: any) => (
                                <div key={sec.id} className="mt-1 text-xs text-muted-foreground">
                                  {sec.name_ar}: {sec.batches?.length || 0} دفعة
                                </div>
                              ))}
                            </div>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}
