import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { Plus, Trash2, Edit, Download, Clock, FileText } from 'lucide-react'
import { assignmentApi, courseApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { useAuthStore } from '@/store/auth'
import { formatDateTime, formatBytes } from '@/lib/utils'
import type { Assignment } from '@/types'

export default function AssignmentsPage() {
  const { t, i18n } = useTranslation()
  const { user } = useAuthStore()
  const qc = useQueryClient()
  const [showForm, setShowForm] = useState(false)
  const [editing, setEditing] = useState<Assignment | null>(null)
  const canManage = user?.role === 'representative' || user?.role === 'college_admin' || user?.role === 'super_admin'

  const { data, isLoading } = useQuery({
    queryKey: ['assignments'],
    queryFn: () => assignmentApi.list({ per_page: 50 }).then(r => r.data),
  })

  const deleteMutation = useMutation({
    mutationFn: (id: number) => assignmentApi.delete(id),
    onSuccess: () => { toast.success('تم الحذف'); qc.invalidateQueries({ queryKey: ['assignments'] }) },
  })

  const downloadMutation = useMutation({
    mutationFn: ({ aid, atid, name }: { aid: number; atid: number; name: string }) =>
      assignmentApi.download(aid, atid).then(r => {
        const url = URL.createObjectURL(r.data)
        const a = document.createElement('a')
        a.href = url; a.download = name; a.click()
        URL.revokeObjectURL(url)
      }),
  })

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{t('nav.assignments')}</h1>
          <p className="text-muted-foreground">{data?.data?.length || 0} {t('common.results')}</p>
        </div>
        {canManage && <Button onClick={() => { setEditing(null); setShowForm(true) }}><Plus className="h-4 w-4" /> {t('assignments.deadline')}</Button>}
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {isLoading ? (
          Array.from({ length: 3 }).map((_, i) => <Card key={i}><CardContent className="p-6"><div className="skeleton h-48 rounded" /></CardContent></Card>)
        ) : data?.data?.map((a: Assignment) => {
          const overdue = new Date(a.deadline).getTime() < Date.now()
          return (
            <Card key={a.id} className="card-hover">
              <CardContent className="p-5">
                <div className="flex items-start justify-between gap-2 mb-3">
                  <h3 className="font-bold flex-1">{a.title}</h3>
                  <Badge variant={overdue ? 'destructive' : 'success'}>{overdue ? t('assignments.overdue') : t('assignments.active')}</Badge>
                </div>
                {a.course && <Badge variant="outline" className="mb-2">{a.course.code}</Badge>}
                {a.description && <p className="text-sm text-muted-foreground line-clamp-3 mb-3">{a.description.replace(/<[^>]+>/g, '')}</p>}
                <div className="flex items-center gap-2 text-sm text-muted-foreground mb-3">
                  <Clock className="h-4 w-4" />
                  <span>{formatDateTime(a.deadline, i18n.language)}</span>
                </div>
                {a.attachments && a.attachments.length > 0 && (
                  <div className="space-y-1 mb-3">
                    {a.attachments.map(att => (
                      <button key={att.id} onClick={() => downloadMutation.mutate({ aid: a.id, atid: att.id, name: att.original_name })}
                        className="flex items-center justify-between w-full rounded border p-2 text-xs hover:bg-accent transition">
                        <span className="truncate flex items-center gap-2"><FileText className="h-3 w-3" /> {att.original_name}</span>
                        <span className="text-muted-foreground">{formatBytes(att.file_size)}</span>
                      </button>
                    ))}
                  </div>
                )}
                {canManage && (
                  <div className="flex gap-2 pt-2 border-t">
                    <Button variant="ghost" size="sm" onClick={() => { setEditing(a); setShowForm(true) }}><Edit className="h-4 w-4" /></Button>
                    <Button variant="ghost" size="sm" onClick={() => { if (confirm('تأكيد الحذف؟')) deleteMutation.mutate(a.id) }}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                  </div>
                )}
              </CardContent>
            </Card>
          )
        })}
      </div>

      <Dialog open={showForm} onOpenChange={setShowForm}>
        <DialogContent className="max-w-2xl">
          <AssignmentForm assignment={editing} onClose={() => setShowForm(false)} onSuccess={() => { setShowForm(false); qc.invalidateQueries({ queryKey: ['assignments'] }) }} />
        </DialogContent>
      </Dialog>
    </div>
  )
}

function AssignmentForm({ assignment, onClose, onSuccess }: { assignment: Assignment | null; onClose: () => void; onSuccess: () => void }) {
  const { t } = useTranslation()
  const [loading, setLoading] = useState(false)
  const { data: courses } = useQuery({
    queryKey: ['courses'],
    queryFn: () => courseApi.list({ per_page: 100 }).then(r => r.data),
  })
  const [form, setForm] = useState({
    title: assignment?.title || '',
    description: assignment?.description || '',
    course_id: assignment?.course_id?.toString() || '',
    deadline: assignment?.deadline ? assignment.deadline.slice(0, 16) : '',
    max_grade: assignment?.max_grade || 100,
    allow_late_submission: assignment?.allow_late_submission || false,
    late_penalty_percent: assignment?.late_penalty_percent || 0,
    notify_telegram: assignment?.notify_telegram ?? true,
    attachments: [] as File[],
  })

  const submit = async () => {
    if (!form.title.trim() || !form.deadline) { toast.error('العنوان والموعد النهائي مطلوبان'); return }
    setLoading(true)
    try {
      const fd = new FormData()
      fd.append('title', form.title)
      fd.append('description', form.description)
      if (form.course_id) fd.append('course_id', form.course_id)
      fd.append('deadline', form.deadline)
      fd.append('max_grade', String(form.max_grade))
      fd.append('allow_late_submission', String(form.allow_late_submission))
      fd.append('late_penalty_percent', String(form.late_penalty_percent))
      fd.append('notify_telegram', String(form.notify_telegram))
      form.attachments.forEach(f => fd.append('attachments[]', f))
      if (assignment) {
        await assignmentApi.update(assignment.id, fd)
        toast.success('تم التحديث')
      } else {
        await assignmentApi.create(fd)
        toast.success('تم الإنشاء')
      }
      onSuccess()
    } catch (e: any) {
      toast.error(e?.response?.data?.message || 'فشل العملية')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <DialogHeader><DialogTitle>{assignment ? t('common.edit') : t('common.create')}</DialogTitle></DialogHeader>
      <div className="space-y-3 max-h-[60vh] overflow-y-auto">
        <div className="space-y-2"><Label>{t('common.title')} *</Label><Input value={form.title} onChange={e => setForm({ ...form, title: e.target.value })} /></div>
        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-2"><Label>المقرر</Label>
            <Select value={form.course_id} onValueChange={v => setForm({ ...form, course_id: v })}>
              <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
              <SelectContent>{courses?.data?.map((c: any) => <SelectItem key={c.id} value={String(c.id)}>{c.code} - {c.name_ar}</SelectItem>)}</SelectContent>
            </Select>
          </div>
          <div className="space-y-2"><Label>{t('assignments.deadline')} *</Label><Input type="datetime-local" value={form.deadline} onChange={e => setForm({ ...form, deadline: e.target.value })} /></div>
        </div>
        <div className="space-y-2"><Label>{t('common.description')}</Label><Textarea rows={4} value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} /></div>
        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-2"><Label>{t('assignments.maxGrade')}</Label><Input type="number" value={form.max_grade} onChange={e => setForm({ ...form, max_grade: Number(e.target.value) })} /></div>
          <div className="space-y-2"><Label>{t('assignments.latePenalty')} %</Label><Input type="number" value={form.late_penalty_percent} onChange={e => setForm({ ...form, late_penalty_percent: Number(e.target.value) })} /></div>
        </div>
        <div className="flex items-center gap-4">
          <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.allow_late_submission} onChange={e => setForm({ ...form, allow_late_submission: e.target.checked })} /> {t('assignments.allowLate')}</label>
          <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.notify_telegram} onChange={e => setForm({ ...form, notify_telegram: e.target.checked })} /> إشعار تيليجرام</label>
        </div>
        <div className="space-y-2"><Label>{t('announcements.attachments')}</Label><Input type="file" multiple onChange={e => setForm({ ...form, attachments: Array.from(e.target.files || []) })} /></div>
      </div>
      <DialogFooter>
        <Button variant="outline" onClick={onClose}>{t('common.cancel')}</Button>
        <Button onClick={submit} disabled={loading}>{loading ? t('common.loading') : t('common.save')}</Button>
      </DialogFooter>
    </>
  )
}
