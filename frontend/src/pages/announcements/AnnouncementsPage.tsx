import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { motion } from 'framer-motion'
import { Plus, Pin, PinOff, Trash2, Edit, Eye, Filter, Search, Calendar } from 'lucide-react'
import { announcementApi, courseApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { useAuthStore } from '@/store/auth'
import { timeAgo, formatBytes } from '@/lib/utils'
import type { Announcement, AnnouncementType } from '@/types'

const TYPE_COLORS: Record<string, string> = {
  holiday: 'bg-emerald-500', assignment: 'bg-blue-500', lecture: 'bg-violet-500', schedule: 'bg-amber-500',
  general: 'bg-slate-500', urgent: 'bg-red-500', emergency: 'bg-rose-600', meeting: 'bg-cyan-500', important: 'bg-orange-500',
}

export default function AnnouncementsPage() {
  const { t } = useTranslation()
  const { user } = useAuthStore()
  const qc = useQueryClient()
  const [search, setSearch] = useState('')
  const [typeFilter, setTypeFilter] = useState<string>('all')
  const [showCreate, setShowCreate] = useState(false)
  const [viewing, setViewing] = useState<Announcement | null>(null)
  const [editing, setEditing] = useState<Announcement | null>(null)

  const canManage = user?.role === 'representative' || user?.role === 'college_admin' || user?.role === 'super_admin'

  const { data, isLoading } = useQuery({
    queryKey: ['announcements', search, typeFilter],
    queryFn: () => announcementApi.list({ q: search, type: typeFilter !== 'all' ? typeFilter : undefined, per_page: 50 }).then(r => r.data),
  })

  const { data: coursesData } = useQuery({
    queryKey: ['courses'],
    queryFn: () => courseApi.list({ per_page: 100 }).then(r => r.data),
    enabled: canManage,
  })

  const deleteMutation = useMutation({
    mutationFn: (id: number) => announcementApi.delete(id),
    onSuccess: () => { toast.success(t('announcements.deleted') || 'تم الحذف'); qc.invalidateQueries({ queryKey: ['announcements'] }) },
  })

  const pinMutation = useMutation({
    mutationFn: (id: number) => announcementApi.togglePin(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['announcements'] }),
  })

  const announcements: Announcement[] = data?.data || []
  const sorted = [...announcements].sort((a, b) => (b.is_pinned ? 1 : 0) - (a.is_pinned ? 1 : 0))

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{t('nav.announcements')}</h1>
          <p className="text-muted-foreground">{announcements.length} {t('common.results')}</p>
        </div>
        {canManage && (
          <Button onClick={() => { setEditing(null); setShowCreate(true) }}>
            <Plus className="h-4 w-4" /> {t('announcements.create')}
          </Button>
        )}
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col md:flex-row gap-3">
            <div className="relative flex-1">
              <Search className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input placeholder={t('common.search')} className="pr-10" value={search} onChange={e => setSearch(e.target.value)} />
            </div>
            <Select value={typeFilter} onValueChange={setTypeFilter}>
              <SelectTrigger className="md:w-48"><SelectValue placeholder={t('common.filter')} /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{t('common.all')}</SelectItem>
                {Object.entries({
                  holiday: 'عطلة', assignment: 'واجب', lecture: 'محاضرة', schedule: 'جدول',
                  general: 'عام', urgent: 'عاجل', emergency: 'طوارئ', meeting: 'اجتماع', important: 'مهم',
                }).map(([v, l]) => <SelectItem key={v} value={v}>{l}</SelectItem>)}
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      {/* List */}
      <div className="grid gap-4">
        {isLoading ? (
          Array.from({ length: 3 }).map((_, i) => <Card key={i}><CardContent className="p-6"><div className="skeleton h-32 rounded-lg" /></CardContent></Card>)
        ) : sorted.length === 0 ? (
          <Card><CardContent className="p-12 text-center text-muted-foreground">{t('common.noData')}</CardContent></Card>
        ) : (
          sorted.map((a, i) => (
            <motion.div key={a.id} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.03 }}>
              <Card className={`card-hover ${a.is_pinned ? 'border-primary/40 bg-primary/5' : ''}`}>
                <CardContent className="p-5">
                  <div className="flex items-start gap-3">
                    <div className={`mt-1 h-3 w-3 rounded-full ${TYPE_COLORS[a.type]}`} />
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 flex-wrap">
                        {a.is_pinned && <Pin className="h-4 w-4 text-primary" />}
                        <h3 className="font-bold text-lg">{a.title}</h3>
                        <Badge variant="secondary">{t(`announcements.types.${a.type}`)}</Badge>
                        {a.course && <Badge variant="outline">{a.course.name_ar}</Badge>}
                      </div>
                      <p className="text-muted-foreground mt-2 line-clamp-2">{a.body.replace(/<[^>]+>/g, '')}</p>
                      <div className="flex items-center gap-4 mt-3 text-xs text-muted-foreground">
                        <span>{a.author?.name_ar || a.author?.name}</span>
                        <span>{timeAgo(a.published_at || a.created_at)}</span>
                        {a.attachments && a.attachments.length > 0 && (
                          <span>📎 {a.attachments.length}</span>
                        )}
                      </div>
                    </div>
                    <div className="flex flex-col gap-1">
                      <Button variant="ghost" size="icon" onClick={() => setViewing(a)}><Eye className="h-4 w-4" /></Button>
                      {canManage && (
                        <>
                          <Button variant="ghost" size="icon" onClick={() => pinMutation.mutate(a.id)}>
                            {a.is_pinned ? <PinOff className="h-4 w-4" /> : <Pin className="h-4 w-4" />}
                          </Button>
                          <Button variant="ghost" size="icon" onClick={() => { setEditing(a); setShowCreate(true) }}><Edit className="h-4 w-4" /></Button>
                          <Button variant="ghost" size="icon" onClick={() => { if (confirm('تأكيد الحذف؟')) deleteMutation.mutate(a.id) }}><Trash2 className="h-4 w-4 text-destructive" /></Button>
                        </>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          ))
        )}
      </div>

      {/* Create/Edit Dialog */}
      <Dialog open={showCreate} onOpenChange={setShowCreate}>
        <DialogContent className="max-w-2xl">
          <AnnouncementForm
            announcement={editing}
            courses={coursesData?.data || []}
            onClose={() => setShowCreate(false)}
            onSuccess={() => { setShowCreate(false); qc.invalidateQueries({ queryKey: ['announcements'] }) }}
          />
        </DialogContent>
      </Dialog>

      {/* View Dialog */}
      <Dialog open={!!viewing} onOpenChange={(o) => !o && setViewing(null)}>
        <DialogContent className="max-w-2xl">
          {viewing && <AnnouncementDetail announcement={viewing} />}
        </DialogContent>
      </Dialog>
    </div>
  )
}

function AnnouncementForm({ announcement, courses, onClose, onSuccess }: {
  announcement: Announcement | null
  courses: any[]
  onClose: () => void
  onSuccess: () => void
}) {
  const { t } = useTranslation()
  const qc = useQueryClient()
  const [loading, setLoading] = useState(false)
  const [form, setForm] = useState({
    title: announcement?.title || '',
    body: announcement?.body || '',
    type: (announcement?.type || 'general') as AnnouncementType,
    course_id: announcement?.course_id?.toString() || '',
    is_pinned: announcement?.is_pinned || false,
    send_telegram: announcement?.send_telegram || false,
    scheduled_at: '',
    attachments: [] as File[],
  })

  const submit = async () => {
    if (!form.title.trim() || !form.body.trim()) { toast.error('العنوان والمحتوى مطلوبان'); return }
    setLoading(true)
    try {
      const fd = new FormData()
      fd.append('title', form.title)
      fd.append('body', form.body)
      fd.append('type', form.type)
      fd.append('is_pinned', String(form.is_pinned))
      fd.append('send_telegram', String(form.send_telegram))
      if (form.course_id) fd.append('course_id', form.course_id)
      if (form.scheduled_at) {
        fd.append('scheduled_at', form.scheduled_at)
        fd.append('is_published', 'false')
      }
      form.attachments.forEach(f => fd.append('attachments[]', f))
      if (announcement) {
        await announcementApi.update(announcement.id, fd)
        toast.success('تم التحديث')
      } else {
        await announcementApi.create(fd)
        toast.success('تم النشر')
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
      <DialogHeader>
        <DialogTitle>{announcement ? t('announcements.edit') : t('announcements.create')}</DialogTitle>
      </DialogHeader>
      <div className="space-y-4 max-h-[60vh] overflow-y-auto scrollbar-thin">
        <div className="space-y-2">
          <Label>{t('common.title')}</Label>
          <Input value={form.title} onChange={e => setForm({ ...form, title: e.target.value })} />
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-2">
            <Label>{t('common.type')}</Label>
            <Select value={form.type} onValueChange={v => setForm({ ...form, type: v as AnnouncementType })}>
              <SelectTrigger><SelectValue /></SelectTrigger>
              <SelectContent>
                {Object.entries({ holiday: 'عطلة', assignment: 'واجب', lecture: 'محاضرة', schedule: 'جدول', general: 'عام', urgent: 'عاجل', emergency: 'طوارئ', meeting: 'اجتماع', important: 'مهم' }).map(([v, l]) => <SelectItem key={v} value={v}>{l}</SelectItem>)}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label>المقرر</Label>
            <Select value={form.course_id} onValueChange={v => setForm({ ...form, course_id: v })}>
              <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
              <SelectContent>
                {courses.map((c: any) => <SelectItem key={c.id} value={String(c.id)}>{c.code} - {c.name_ar}</SelectItem>)}
              </SelectContent>
            </Select>
          </div>
        </div>
        <div className="space-y-2">
          <Label>{t('common.description')}</Label>
          <Textarea rows={6} value={form.body} onChange={e => setForm({ ...form, body: e.target.value })} />
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div className="space-y-2">
            <Label>{t('announcements.schedule')} (اختياري)</Label>
            <Input type="datetime-local" value={form.scheduled_at} onChange={e => setForm({ ...form, scheduled_at: e.target.value })} />
          </div>
        </div>
        <div className="flex items-center gap-4">
          <label className="flex items-center gap-2 text-sm cursor-pointer">
            <input type="checkbox" checked={form.is_pinned} onChange={e => setForm({ ...form, is_pinned: e.target.checked })} className="rounded" />
            {t('announcements.pin')}
          </label>
          <label className="flex items-center gap-2 text-sm cursor-pointer">
            <input type="checkbox" checked={form.send_telegram} onChange={e => setForm({ ...form, send_telegram: e.target.checked })} className="rounded" />
            {t('announcements.sendTelegram')}
          </label>
        </div>
        <div className="space-y-2">
          <Label>{t('announcements.attachments')}</Label>
          <Input type="file" multiple onChange={e => setForm({ ...form, attachments: Array.from(e.target.files || []) })} />
          {form.attachments.length > 0 && (
            <ul className="text-xs text-muted-foreground space-y-1 mt-2">
              {form.attachments.map((f, i) => <li key={i}>📎 {f.name} ({formatBytes(f.size)})</li>)}
            </ul>
          )}
        </div>
      </div>
      <DialogFooter>
        <Button variant="outline" onClick={onClose}>{t('common.cancel')}</Button>
        <Button onClick={submit} disabled={loading}>{loading ? t('common.loading') : t('common.save')}</Button>
      </DialogFooter>
    </>
  )
}

function AnnouncementDetail({ announcement }: { announcement: Announcement }) {
  const { t } = useTranslation()
  return (
    <>
      <DialogHeader>
        <DialogTitle className="flex items-center gap-2 flex-wrap">
          <span className={`h-3 w-3 rounded-full ${TYPE_COLORS[announcement.type]}`} />
          {announcement.title}
        </DialogTitle>
      </DialogHeader>
      <div className="space-y-4 max-h-[60vh] overflow-y-auto scrollbar-thin">
        <div className="flex items-center gap-2 flex-wrap">
          <Badge variant="secondary">{t(`announcements.types.${announcement.type}`)}</Badge>
          {announcement.course && <Badge variant="outline">{announcement.course.name_ar}</Badge>}
          {announcement.is_pinned && <Badge variant="default">{t('announcements.pin')}</Badge>}
        </div>
        <div className="prose prose-sm max-w-none dark:prose-invert">
          <p className="whitespace-pre-wrap">{announcement.body.replace(/<[^>]+>/g, '')}</p>
        </div>
        <div className="text-xs text-muted-foreground">
          <p>نشر بواسطة: {announcement.author?.name_ar || announcement.author?.name}</p>
          <p>التاريخ: {timeAgo(announcement.published_at || announcement.created_at)}</p>
        </div>
        {announcement.attachments && announcement.attachments.length > 0 && (
          <div>
            <p className="text-sm font-medium mb-2">{t('announcements.attachments')}</p>
            <ul className="space-y-1">
              {announcement.attachments.map(att => (
                <li key={att.id} className="flex items-center justify-between rounded-md border p-2">
                  <span className="text-sm">{att.original_name}</span>
                  <span className="text-xs text-muted-foreground">{formatBytes(att.file_size)}</span>
                </li>
              ))}
            </ul>
          </div>
        )}
      </div>
    </>
  )
}
