import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { Mail, ArrowRight, GraduationCap } from 'lucide-react'
import { authApi } from '@/lib/api-services'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const schema = z.object({ email: z.string().email() })
type FormData = z.infer<typeof schema>

export default function ForgotPasswordPage() {
  const { t } = useTranslation()
  const [loading, setLoading] = useState(false)
  const [sent, setSent] = useState(false)
  const { register, handleSubmit, formState: { errors } } = useForm<FormData>({ resolver: zodResolver(schema) })

  const onSubmit = async (data: FormData) => {
    setLoading(true)
    try {
      await authApi.forgotPassword(data.email)
      setSent(true)
      toast.success(t('auth.resetLinkSent'))
    } catch (e: any) {
      toast.error(e?.response?.data?.message || 'فشل الإرسال')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/30 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        <div className="text-center mb-6">
          <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-lg">
            <GraduationCap className="h-7 w-7" />
          </div>
          <h1 className="text-2xl font-bold">{t('auth.forgotPassword')}</h1>
        </div>

        {sent ? (
          <div className="glass rounded-2xl p-8 text-center">
            <p className="text-emerald-600 font-medium mb-2">✅ {t('auth.resetLinkSent')}</p>
            <p className="text-sm text-muted-foreground mb-6">تحقق من بريدك الإلكتروني واتبع التعليمات.</p>
            <Link to="/auth/login"><Button variant="outline" className="w-full">العودة لتسجيل الدخول</Button></Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit(onSubmit)} className="glass rounded-2xl p-8 space-y-4 shadow-xl">
            <div className="space-y-2">
              <Label>{t('common.email')}</Label>
              <div className="relative">
                <Mail className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input type="email" className="pr-10" placeholder="you@example.com" {...register('email')} />
              </div>
              {errors.email && <p className="text-xs text-destructive">{errors.email.message}</p>}
            </div>
            <Button type="submit" disabled={loading} className="w-full">{loading ? t('common.loading') : t('auth.forgotPassword')}</Button>
            <Link to="/auth/login" className="block text-center text-sm text-primary hover:underline">
              <span className="inline-flex items-center gap-1"><ArrowRight className="h-3 w-3" /> {t('auth.login')}</span>
            </Link>
          </form>
        )}
      </div>
    </div>
  )
}
