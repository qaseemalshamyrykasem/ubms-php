import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { User, Mail, Phone, Save, Lock } from 'lucide-react'
import { useAuthStore } from '@/store/auth'
import { authApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { useUIStore } from '@/store/ui'
import { initials } from '@/lib/utils'

export default function ProfilePage() {
  const { t } = useTranslation()
  const { user, updateUser } = useAuthStore()
  const { theme, setTheme, locale, setLocale } = useUIStore()
  const { i18n } = useTranslation()
  const [loading, setLoading] = useState(false)
  const [passLoading, setPassLoading] = useState(false)
  const [form, setForm] = useState({
    name: user?.name || '',
    name_ar: user?.name_ar || '',
    phone: user?.phone || '',
    avatar: null as File | null,
  })
  const [pwd, setPwd] = useState({ current_password: '', password: '', password_confirmation: '' })

  const saveProfile = async () => {
    setLoading(true)
    try {
      const fd = new FormData()
      fd.append('name', form.name)
      if (form.name_ar) fd.append('name_ar', form.name_ar)
      if (form.phone) fd.append('phone', form.phone)
      if (form.avatar) fd.append('avatar', form.avatar)
      const r = await authApi.updateProfile(fd)
      updateUser(r.data.user)
      toast.success(t('common.save'))
    } catch (e: any) {
      toast.error(e?.response?.data?.message || 'فشل الحفظ')
    } finally {
      setLoading(false)
    }
  }

  const changePassword = async () => {
    if (pwd.password !== pwd.password_confirmation) { toast.error('كلمتا المرور غير متطابقتين'); return }
    setPassLoading(true)
    try {
      await authApi.changePassword(pwd)
      toast.success('تم تغيير كلمة المرور')
      setPwd({ current_password: '', password: '', password_confirmation: '' })
    } catch (e: any) {
      toast.error(e?.response?.data?.message || 'فشل التغيير')
    } finally {
      setPassLoading(false)
    }
  }

  return (
    <div className="space-y-6 max-w-4xl">
      <div>
        <h1 className="text-2xl font-bold">{t('common.profile')}</h1>
        <p className="text-muted-foreground">إدارة معلوماتك الشخصية والإعدادات</p>
      </div>

      {/* Profile info */}
      <Card>
        <CardHeader><CardTitle>المعلومات الشخصية</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center gap-4">
            <Avatar className="h-20 w-20 ring-4 ring-border">
              <AvatarImage src={user?.avatar} />
              <AvatarFallback className="text-xl">{initials(user?.name_ar || user?.name)}</AvatarFallback>
            </Avatar>
            <div>
              <p className="font-bold text-lg">{user?.name_ar || user?.name}</p>
              <p className="text-sm text-muted-foreground">{user?.email}</p>
              <label className="inline-block mt-2">
                <input type="file" accept="image/*" className="hidden" onChange={e => setForm({ ...form, avatar: e.target.files?.[0] || null })} />
                <span className="cursor-pointer text-sm text-primary hover:underline">تغيير الصورة</span>
              </label>
            </div>
          </div>

          <div className="grid md:grid-cols-2 gap-4">
            <div className="space-y-2"><Label>{t('auth.fullName')}</Label>
              <div className="relative"><User className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input className="pr-10" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} /></div>
            </div>
            <div className="space-y-2"><Label>{t('auth.arabicName')}</Label><Input value={form.name_ar} onChange={e => setForm({ ...form, name_ar: e.target.value })} dir="rtl" /></div>
            <div className="space-y-2"><Label>{t('common.email')}</Label>
              <div className="relative"><Mail className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input className="pr-10" value={user?.email || ''} disabled /></div>
            </div>
            <div className="space-y-2"><Label>{t('common.phone')}</Label>
              <div className="relative"><Phone className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input className="pr-10" value={form.phone} onChange={e => setForm({ ...form, phone: e.target.value })} /></div>
            </div>
          </div>

          <Button onClick={saveProfile} disabled={loading}><Save className="h-4 w-4" /> {loading ? t('common.loading') : t('common.save')}</Button>
        </CardContent>
      </Card>

      {/* Preferences */}
      <Card>
        <CardHeader><CardTitle>التفضيلات</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <p className="font-medium">الوضع الداكن</p>
              <p className="text-xs text-muted-foreground">{theme === 'dark' ? t('theme.dark') : t('theme.light')}</p>
            </div>
            <Switch checked={theme === 'dark'} onCheckedChange={(c) => setTheme(c ? 'dark' : 'light')} />
          </div>
          <div className="flex items-center justify-between">
            <div>
              <p className="font-medium">اللغة</p>
              <p className="text-xs text-muted-foreground">{locale === 'ar' ? 'العربية' : 'English'}</p>
            </div>
            <Switch checked={locale === 'ar'} onCheckedChange={(c) => { const l = c ? 'ar' : 'en'; setLocale(l); i18n.changeLanguage(l) }} />
          </div>
        </CardContent>
      </Card>

      {/* Password */}
      <Card>
        <CardHeader><CardTitle>تغيير كلمة المرور</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <div className="space-y-2"><Label>{t('common.password')} الحالية</Label>
            <div className="relative"><Lock className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input type="password" className="pr-10" value={pwd.current_password} onChange={e => setPwd({ ...pwd, current_password: e.target.value })} /></div>
          </div>
          <div className="grid md:grid-cols-2 gap-3">
            <div className="space-y-2"><Label>الجديدة</Label>
              <div className="relative"><Lock className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input type="password" className="pr-10" value={pwd.password} onChange={e => setPwd({ ...pwd, password: e.target.value })} /></div>
            </div>
            <div className="space-y-2"><Label>التأكيد</Label>
              <div className="relative"><Lock className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" /><Input type="password" className="pr-10" value={pwd.password_confirmation} onChange={e => setPwd({ ...pwd, password_confirmation: e.target.value })} /></div>
            </div>
          </div>
          <Button onClick={changePassword} disabled={passLoading}><Save className="h-4 w-4" /> {passLoading ? t('common.loading') : 'تغيير'}</Button>
        </CardContent>
      </Card>
    </div>
  )
}
