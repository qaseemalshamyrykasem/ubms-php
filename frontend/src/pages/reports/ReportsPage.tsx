import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { FileSpreadsheet, FileText, Download } from 'lucide-react'
import { reportApi, structureApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useAuthStore } from '@/store/auth'
import { useQuery } from '@tanstack/react-query'

export default function ReportsPage() {
  const { t } = useTranslation()
  const { user } = useAuthStore()
  const batchId = user?.representative?.batch?.id || user?.student?.batch?.id
  const [filters, setFilters] = useState({ course_id: '', from: '', to: '' })

  const download = async (type: 'attendance_excel' | 'attendance_pdf' | 'students' | 'announcements' | 'assignments' | 'statistics', title: string) => {
    if (!batchId) { toast.error('دفعتك غير محددة'); return }
    try {
      let blob: Blob
      if (type === 'attendance_excel') {
        const r = await reportApi.attendance(batchId, 'excel', filters)
        blob = r.data
      } else if (type === 'attendance_pdf') {
        const r = await reportApi.attendance(batchId, 'pdf', filters)
        blob = r.data
      } else if (type === 'students') {
        blob = (await reportApi.students(batchId)).data
      } else if (type === 'announcements') {
        blob = (await reportApi.announcements(batchId)).data
      } else if (type === 'assignments') {
        blob = (await reportApi.assignments(batchId)).data
      } else {
        blob = (await reportApi.statistics(batchId)).data
      }
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      const ext = type.endsWith('pdf') ? 'pdf' : 'xlsx'
      a.href = url; a.download = `${title}_${batchId}.${ext}`; a.click()
      URL.revokeObjectURL(url)
      toast.success('تم تحميل التقرير')
    } catch (e: any) {
      toast.error(e?.response?.data?.message || 'فشل التصدير')
    }
  }

  const reports = [
    { key: 'attendance_excel', title: t('reports.attendance'), desc: 'Excel - تفصيلي', icon: FileSpreadsheet, color: 'from-emerald-500 to-teal-500' },
    { key: 'attendance_pdf', title: t('reports.attendance'), desc: 'PDF - احترافي', icon: FileText, color: 'from-red-500 to-pink-500' },
    { key: 'students', title: t('reports.students'), desc: 'Excel', icon: FileSpreadsheet, color: 'from-blue-500 to-indigo-500' },
    { key: 'announcements', title: t('reports.announcements'), desc: 'Excel', icon: FileSpreadsheet, color: 'from-purple-500 to-pink-500' },
    { key: 'assignments', title: t('reports.assignments'), desc: 'Excel', icon: FileSpreadsheet, color: 'from-amber-500 to-orange-500' },
    { key: 'statistics', title: t('reports.statistics'), desc: 'Excel', icon: FileSpreadsheet, color: 'from-cyan-500 to-blue-500' },
  ] as const

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('reports.title')}</h1>
        <p className="text-muted-foreground">تصدير التقارير بصيغ Excel و PDF</p>
      </div>

      {/* Filters */}
      <Card>
        <CardHeader><CardTitle>{t('common.filter')}</CardTitle></CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-3 gap-3">
            <div className="space-y-2"><Label>{t('reports.dateFrom')}</Label><Input type="date" value={filters.from} onChange={e => setFilters({ ...filters, from: e.target.value })} /></div>
            <div className="space-y-2"><Label>{t('reports.dateTo')}</Label><Input type="date" value={filters.to} onChange={e => setFilters({ ...filters, to: e.target.value })} /></div>
          </div>
        </CardContent>
      </Card>

      {/* Reports grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {reports.map((r) => (
          <Card key={r.key} className="card-hover cursor-pointer" onClick={() => download(r.key as any, r.title)}>
            <CardContent className="p-5">
              <div className="flex items-start gap-3">
                <div className={`flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${r.color} text-white`}>
                  <r.icon className="h-6 w-6" />
                </div>
                <div className="flex-1">
                  <h3 className="font-bold">{r.title}</h3>
                  <p className="text-sm text-muted-foreground">{r.desc}</p>
                </div>
                <Download className="h-4 w-4 text-muted-foreground" />
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  )
}
