import { useState } from 'react'
import { useQuery, useMutation } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { motion } from 'framer-motion'
import { Plus, RefreshCw, Lock, QrCode, Users, BarChart3 } from 'lucide-react'
import { attendanceApi, courseApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { useAuthStore } from '@/store/auth'
import { formatDate } from '@/lib/utils'

export default function AttendancePage() {
  const { t } = useTranslation()
  const { user } = useAuthStore()
  const isStudent = user?.role === 'student'
  const [tab, setTab] = useState(isStudent ? 'my' : 'lectures')
  const [showCreate, setShowCreate] = useState(false)
  const [selectedLecture, setSelectedLecture] = useState<number | null>(null)

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{t('nav.attendance')}</h1>
          <p className="text-muted-foreground">{t('attendance.history')}</p>
        </div>
        {!isStudent && (
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="h-4 w-4" /> {t('attendance.createLecture')}
          </Button>
        )}
      </div>

      <Tabs value={tab} onValueChange={setTab}>
        <TabsList>
          {isStudent ? (
            <TabsTrigger value="my">{t('attendance.myStats')}</TabsTrigger>
          ) : (
            <>
              <TabsTrigger value="lectures">المحاضرات</TabsTrigger>
              <TabsTrigger value="batch">{t('attendance.batchStats')}</TabsTrigger>
            </>
          )}
        </TabsList>

        {isStudent ? (
          <TabsContent value="my"><MyStats /></TabsContent>
        ) : (
          <>
            <TabsContent value="lectures"><LecturesList onSelect={setSelectedLecture} /></TabsContent>
            <TabsContent value="batch"><BatchStats /></TabsContent>
          </>
        )}
      </Tabs>

      <Dialog open={showCreate} onOpenChange={setShowCreate}>
        <DialogContent>
          <CreateLectureForm onClose={() => setShowCreate(false)} />
        </DialogContent>
      </Dialog>

      <Dialog open={!!selectedLecture} onOpenChange={(o) => !o && setSelectedLecture(null)}>
        <DialogContent className="max-w-4xl">
          {selectedLecture && <LectureDetail lectureId={selectedLecture} />}
        </DialogContent>
      </Dialog>
    </div>
  )
}

function MyStats() {
  const { t } = useTranslation()
  const { data: stats } = useQuery({
    queryKey: ['attendance', 'my-stats'],
    queryFn: () => attendanceApi.myStats().then(r => r.data),
  })
  const { data: history } = useQuery({
    queryKey: ['attendance', 'my-history'],
    queryFn: () => attendanceApi.myHistory({ per_page: 20 }).then(r => r.data),
  })

  if (!stats) return null
  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-5">
        {[
          { label: 'الإجمالي', value: stats.total_lectures, color: 'text-slate-600' },
          { label: t('attendance.present'), value: stats.present, color: 'text-emerald-600' },
          { label: t('attendance.late'), value: stats.late, color: 'text-amber-600' },
          { label: t('attendance.absent'), value: stats.absent, color: 'text-red-600' },
          { label: t('attendance.excused'), value: stats.excused, color: 'text-blue-600' },
        ].map(s => (
          <Card key={s.label}><CardContent className="p-4 text-center">
            <p className={`text-3xl font-bold ${s.color}`}>{s.value}</p>
            <p className="text-xs text-muted-foreground mt-1">{s.label}</p>
          </CardContent></Card>
        ))}
      </div>

      <Card>
        <CardHeader><CardTitle>{t('attendance.attendanceRate')}</CardTitle></CardHeader>
        <CardContent>
          <Progress value={stats.rate} indicatorClassName="bg-gradient-to-r from-emerald-500 to-teal-500" />
          <p className="text-center mt-2 text-2xl font-bold">{stats.rate}%</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader><CardTitle>{t('attendance.history')}</CardTitle></CardHeader>
        <CardContent className="space-y-2">
          {history?.data?.map((h: any) => (
            <div key={h.id} className="flex items-center justify-between rounded-lg border p-3">
              <div>
                <p className="font-medium text-sm">{h.lecture?.course?.name_ar || '-'}</p>
                <p className="text-xs text-muted-foreground">{formatDate(h.lecture?.date)} • {h.lecture?.start_time}</p>
              </div>
              <Badge variant={h.status === 'present' ? 'success' : h.status === 'absent' ? 'destructive' : 'secondary'}>
                {t(`attendance.status_${h.status}`)}
              </Badge>
            </div>
          ))}
        </CardContent>
      </Card>
    </div>
  )
}

function LecturesList({ onSelect }: { onSelect: (id: number) => void }) {
  const { t } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['attendance', 'lectures'],
    queryFn: () => attendanceApi.lectures({ per_page: 30 }).then(r => r.data),
  })

  if (isLoading) return <div className="skeleton h-64 rounded-xl" />
  return (
    <div className="grid gap-3">
      {data?.data?.map((l: any) => (
        <motion.div key={l.id} initial={{ opacity: 0, y: 5 }} animate={{ opacity: 1, y: 0 }}>
          <Card className="card-hover cursor-pointer" onClick={() => onSelect(l.id)}>
            <CardContent className="p-4 flex items-center justify-between">
              <div>
                <p className="font-medium">{l.course?.name_ar || l.title}</p>
                <p className="text-sm text-muted-foreground">{formatDate(l.date)} • {l.start_time} • {l.room || '—'}</p>
              </div>
              <div className="flex items-center gap-2">
                {l.attendance_locked && <Badge variant="secondary"><Lock className="h-3 w-3" /> مقفل</Badge>}
                <Badge variant="outline">{l.attendances?.length || 0} طالب</Badge>
                <Button variant="ghost" size="icon"><QrCode className="h-4 w-4" /></Button>
              </div>
            </CardContent>
          </Card>
        </motion.div>
      ))}
    </div>
  )
}

function BatchStats() {
  const { t } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['attendance', 'batch-stats'],
    queryFn: () => attendanceApi.batchStats().then(r => r.data),
  })

  if (isLoading) return <div className="skeleton h-64 rounded-xl" />

  const stats = data?.stats || {}
  const students = data?.students || []

  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-5">
        {[
          { label: 'المحاضرات', value: stats.lectures || 0, color: 'text-slate-600' },
          { label: 'السجلات', value: stats.total_records || 0, color: 'text-blue-600' },
          { label: 'حاضر', value: stats.present || 0, color: 'text-emerald-600' },
          { label: 'غائب', value: stats.absent || 0, color: 'text-red-600' },
          { label: 'متأخر', value: stats.late || 0, color: 'text-amber-600' },
        ].map(s => (
          <Card key={s.label}><CardContent className="p-4 text-center">
            <p className={`text-3xl font-bold ${s.color}`}>{s.value}</p>
            <p className="text-xs text-muted-foreground mt-1">{s.label}</p>
          </CardContent></Card>
        ))}
      </div>

      <Card>
        <CardHeader><CardTitle>{t('attendance.studentRates')}</CardTitle></CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b text-muted-foreground">
                  <th className="text-right p-2">الطالب</th>
                  <th className="text-center p-2">الرقم</th>
                  <th className="text-center p-2">حاضر</th>
                  <th className="text-center p-2">غائب</th>
                  <th className="text-center p-2">متأخر</th>
                  <th className="text-center p-2">النسبة</th>
                </tr>
              </thead>
              <tbody>
                {students.map((s: any) => (
                  <tr key={s.student.id} className="border-b hover:bg-accent">
                    <td className="p-2 font-medium">{s.student.name_ar || s.student.name}</td>
                    <td className="p-2 text-center text-muted-foreground">{s.student.student_id}</td>
                    <td className="p-2 text-center text-emerald-600">{s.present}</td>
                    <td className="p-2 text-center text-red-600">{s.absent}</td>
                    <td className="p-2 text-center text-amber-600">{s.late}</td>
                    <td className="p-2 text-center">
                      <Badge variant={s.rate >= 75 ? 'success' : s.rate >= 50 ? 'warning' : 'destructive'}>{s.rate}%</Badge>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

function CreateLectureForm({ onClose }: { onClose: () => void }) {
  const { t } = useTranslation()
  const [loading, setLoading] = useState(false)
  const { data: courses } = useQuery({
    queryKey: ['courses'],
    queryFn: () => courseApi.list({ per_page: 100 }).then(r => r.data),
  })
  const [form, setForm] = useState({
    course_id: '',
    title: '',
    date: new Date().toISOString().slice(0, 10),
    start_time: '09:00',
    end_time: '10:30',
    room: '',
  })

  const submit = async () => {
    if (!form.course_id || !form.date) { toast.error('المقرر والتاريخ مطلوبان'); return }
    setLoading(true)
    try {
      await attendanceApi.createLecture({
        course_id: Number(form.course_id),
        title: form.title,
        date: form.date,
        start_time: form.start_time,
        end_time: form.end_time,
        room: form.room,
      })
      toast.success(t('attendance.lecture_created'))
      onClose()
    } catch (e: any) {
      toast.error(e?.response?.data?.message || 'فشل الإنشاء')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <DialogHeader><DialogTitle>{t('attendance.createLecture')}</DialogTitle></DialogHeader>
      <div className="space-y-3">
        <div className="space-y-2">
          <Label>المقرر</Label>
          <Select value={form.course_id} onValueChange={v => setForm({ ...form, course_id: v })}>
            <SelectTrigger><SelectValue placeholder="اختر المقرر" /></SelectTrigger>
            <SelectContent>
              {courses?.data?.map((c: any) => <SelectItem key={c.id} value={String(c.id)}>{c.code} - {c.name_ar}</SelectItem>)}
            </SelectContent>
          </Select>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-2"><Label>{t('common.date')}</Label><Input type="date" value={form.date} onChange={e => setForm({ ...form, date: e.target.value })} /></div>
          <div className="space-y-2"><Label>{t('common.time')}</Label><Input type="time" value={form.start_time} onChange={e => setForm({ ...form, start_time: e.target.value })} /></div>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-2"><Label>القاعة</Label><Input value={form.room} onChange={e => setForm({ ...form, room: e.target.value })} /></div>
          <div className="space-y-2"><Label>نهاية المحاضرة</Label><Input type="time" value={form.end_time} onChange={e => setForm({ ...form, end_time: e.target.value })} /></div>
        </div>
      </div>
      <DialogFooter>
        <Button variant="outline" onClick={onClose}>{t('common.cancel')}</Button>
        <Button onClick={submit} disabled={loading}>{loading ? t('common.loading') : t('common.create')}</Button>
      </DialogFooter>
    </>
  )
}

function LectureDetail({ lectureId }: { lectureId: number }) {
  const { t } = useTranslation()
  const [qrUrl, setQrUrl] = useState<string>('')
  const { data: lectures } = useQuery({
    queryKey: ['attendance', 'lectures'],
    queryFn: () => attendanceApi.lectures({ per_page: 100 }).then(r => r.data),
  })
  const lecture = lectures?.data?.find((l: any) => l.id === lectureId)

  const loadQr = async () => {
    try {
      const blob = await attendanceApi.qrCode(lectureId)
      setQrUrl(URL.createObjectURL(blob.data))
    } catch (e) {
      toast.error('تعذّر تحميل QR')
    }
  }

  const refreshQr = async () => {
    await attendanceApi.refreshQr(lectureId)
    toast.success('تم تحديث الرمز')
    loadQr()
  }

  const lockLecture = async () => {
    if (!confirm('تأكيد قفل التسجيل؟')) return
    await attendanceApi.lock(lectureId)
    toast.success(t('attendance.attendanceLocked'))
  }

  React.useEffect(() => { loadQr() }, [lectureId])

  if (!lecture) return null

  return (
    <>
      <DialogHeader><DialogTitle>{lecture.course?.name_ar} - {formatDate(lecture.date)}</DialogTitle></DialogHeader>
      <div className="grid md:grid-cols-2 gap-6">
        <div className="text-center space-y-3">
          <p className="text-sm text-muted-foreground">{t('attendance.qrCode')}</p>
          {qrUrl && <img src={qrUrl} alt="QR" className="mx-auto rounded-xl border p-4 bg-white" />}
          <div className="flex gap-2 justify-center">
            <Button onClick={refreshQr} size="sm"><RefreshCw className="h-4 w-4" /> {t('attendance.refreshQr')}</Button>
            {!lecture.attendance_locked && <Button onClick={lockLecture} variant="destructive" size="sm"><Lock className="h-4 w-4" /> {t('attendance.lockAttendance')}</Button>}
          </div>
        </div>
        <div>
          <p className="text-sm font-medium mb-2">{t('attendance.present')} ({lecture.attendances?.length || 0})</p>
          <div className="max-h-72 overflow-y-auto scrollbar-thin space-y-1">
            {lecture.attendances?.map((a: any) => (
              <div key={a.id} className="flex items-center justify-between rounded border p-2 text-sm">
                <span>{a.student?.name_ar || a.student?.name}</span>
                <Badge variant={a.status === 'present' ? 'success' : a.status === 'absent' ? 'destructive' : 'secondary'}>
                  {t(`attendance.status_${a.status}`)}
                </Badge>
              </div>
            ))}
          </div>
        </div>
      </div>
    </>
  )
}

import React from 'react'
