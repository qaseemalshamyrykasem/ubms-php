import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { motion } from 'framer-motion'
import { Mail, Lock, LogIn, Eye, EyeOff, GraduationCap } from 'lucide-react'
import { authApi } from '@/lib/api-services'
import { useAuthStore } from '@/store/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'

const schema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
  remember: z.boolean().optional(),
})

type FormData = z.infer<typeof schema>

export default function LoginPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { setAuth } = useAuthStore()
  const [showPass, setShowPass] = useState(false)
  const [loading, setLoading] = useState(false)

  const { register, handleSubmit, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  })

  const onSubmit = async (data: FormData) => {
    setLoading(true)
    try {
      const res = await authApi.login(data.email, data.password, !!data.remember)
      setAuth(res.data.token, res.data.user)
      toast.success(t('auth.loginSuccess'))
      navigate('/dashboard')
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'فشل تسجيل الدخول')
    } finally {
      setLoading(false)
    }
  }

  return (
    <AuthLayout>
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="w-full max-w-md"
      >
        <div className="text-center mb-8">
          <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-lg">
            <GraduationCap className="h-8 w-8" />
          </div>
          <h1 className="text-3xl font-bold">{t('auth.welcomeBack')}</h1>
          <p className="text-muted-foreground mt-2">{t('auth.loginSubtitle')}</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 glass rounded-2xl p-8 shadow-xl">
          <div className="space-y-2">
            <Label htmlFor="email">{t('common.email')}</Label>
            <div className="relative">
              <Mail className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input id="email" type="email" className="pr-10" placeholder="you@example.com" {...register('email')} />
            </div>
            {errors.email && <p className="text-xs text-destructive">{errors.email.message}</p>}
          </div>

          <div className="space-y-2">
            <Label htmlFor="password">{t('common.password')}</Label>
            <div className="relative">
              <Lock className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input
                id="password"
                type={showPass ? 'text' : 'password'}
                className="pr-10 pl-10"
                placeholder="••••••••"
                {...register('password')}
              />
              <button type="button" onClick={() => setShowPass(s => !s)} className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                {showPass ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
              </button>
            </div>
            {errors.password && <p className="text-xs text-destructive">{errors.password.message}</p>}
          </div>

          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Checkbox id="remember" {...register('remember')} />
              <Label htmlFor="remember" className="text-sm cursor-pointer">{t('auth.rememberMe')}</Label>
            </div>
            <Link to="/auth/forgot-password" className="text-sm text-primary hover:underline">{t('auth.forgotPassword')}</Link>
          </div>

          <Button type="submit" disabled={loading} className="w-full" size="lg">
            <LogIn className="h-5 w-5" />
            {loading ? t('common.loading') : t('auth.login')}
          </Button>

          <div className="text-center text-sm">
            {t('auth.dontHaveAccount')}{' '}
            <Link to="/auth/register" className="text-primary hover:underline font-medium">{t('auth.register')}</Link>
          </div>

          <div className="rounded-lg bg-muted/50 p-3 text-xs text-muted-foreground">
            <p className="font-semibold mb-1">حسابات تجريبية:</p>
            <p>مدير: admin@ubms.local / password</p>
            <p>مدير كلية: college@ubms.local / password</p>
            <p>ممثل: rep@ubms.local / password</p>
            <p>طالب: student1@ubms.local / password</p>
          </div>
        </form>
      </motion.div>
    </AuthLayout>
  )
}

function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen flex">
      {/* Left: gradient panel */}
      <div className="hidden lg:flex lg:w-1/2 gradient-bg relative overflow-hidden">
        <div className="absolute inset-0 opacity-20" style={{ backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 40 40\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M0 0h20v20H0V0zm20 20h20v20H20V20z\'/%3E%3C/g%3E%3C/svg%3E")' }} />
        <div className="relative z-10 flex flex-col justify-between p-12 text-white">
          <div>
            <div className="flex items-center gap-3 mb-2">
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                <GraduationCap className="h-7 w-7" />
              </div>
              <span className="text-2xl font-bold">UBMS</span>
            </div>
            <p className="text-white/80 mt-2">نظام إدارة الدفعات الجامعية</p>
          </div>
          <div>
            <h2 className="text-4xl font-bold leading-tight">
              نظام متكامل<br />
              لإدارة الدفعات الجامعية
            </h2>
            <p className="text-white/80 mt-4 text-lg">
              تواصل فعّال. حضور ذكي. إعلانات فورية. كل ما تحتاجه دفعتك في مكان واحد.
            </p>
          </div>
          <div className="grid grid-cols-3 gap-4 text-center">
            {[
              { label: 'إعلان', value: 'فوري' },
              { label: 'حضور', value: 'QR' },
              { label: 'إشعار', value: 'تيليجرام' },
            ].map((s) => (
              <div key={s.label} className="rounded-xl bg-white/10 backdrop-blur p-4">
                <p className="text-2xl font-bold">{s.value}</p>
                <p className="text-sm text-white/70">{s.label}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Right: form */}
      <div className="flex-1 flex items-center justify-center p-6 bg-background">
        {children}
      </div>
    </div>
  )
}
