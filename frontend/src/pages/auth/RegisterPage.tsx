import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { motion } from 'framer-motion'
import { Mail, Lock, User, Phone, Eye, EyeOff, GraduationCap, IdCard } from 'lucide-react'
import { authApi, structureApi } from '@/lib/api-services'
import { useAuthStore } from '@/store/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useQuery } from '@tanstack/react-query'

const schema = z.object({
  name: z.string().min(3),
  name_ar: z.string().optional(),
  email: z.string().email(),
  password: z.string().min(8),
  password_confirmation: z.string().min(8),
  phone: z.string().optional(),
  batch_id: z.string().optional(),
  student_id: z.string().optional(),
}).refine(d => d.password === d.password_confirmation, { message: 'كلمة المرور غير متطابقة', path: ['password_confirmation'] })

type FormData = z.infer<typeof schema>

export default function RegisterPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const { setAuth } = useAuthStore()
  const [showPass, setShowPass] = useState(false)
  const [loading, setLoading] = useState(false)

  const { data: batchesData } = useQuery({
    queryKey: ['batches', 'all'],
    queryFn: () => structureApi.batches({ per_page: 100 }).then(r => r.data),
  })

  const { register, handleSubmit, setValue, watch, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  })

  const onSubmit = async (data: FormData) => {
    setLoading(true)
    try {
      const res = await authApi.register({
        name: data.name,
        name_ar: data.name_ar,
        email: data.email,
        password: data.password,
        password_confirmation: data.password_confirmation,
        phone: data.phone,
        batch_id: data.batch_id,
        student_id: data.student_id,
      })
      setAuth(res.data.token, res.data.user)
      toast.success(t('auth.registerSuccess'))
      navigate('/dashboard')
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'فشل التسجيل')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950/30 flex items-center justify-center p-4">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="w-full max-w-2xl">
        <div className="text-center mb-6">
          <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-lg">
            <GraduationCap className="h-7 w-7" />
          </div>
          <h1 className="text-2xl font-bold">{t('auth.register')}</h1>
          <p className="text-muted-foreground mt-1">{t('auth.registerSubtitle')}</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 glass rounded-2xl p-8 shadow-xl">
          <div className="grid md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="name">{t('auth.fullName')} *</Label>
              <div className="relative">
                <User className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input id="name" className="pr-10" {...register('name')} />
              </div>
              {errors.name && <p className="text-xs text-destructive">{errors.name.message}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="name_ar">{t('auth.arabicName')}</Label>
              <Input id="name_ar" {...register('name_ar')} dir="rtl" />
            </div>

            <div className="space-y-2">
              <Label htmlFor="email">{t('common.email')} *</Label>
              <div className="relative">
                <Mail className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input id="email" type="email" className="pr-10" {...register('email')} />
              </div>
              {errors.email && <p className="text-xs text-destructive">{errors.email.message}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="phone">{t('common.phone')}</Label>
              <div className="relative">
                <Phone className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input id="phone" className="pr-10" {...register('phone')} />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="password">{t('common.password')} *</Label>
              <div className="relative">
                <Lock className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input id="password" type={showPass ? 'text' : 'password'} className="pr-10 pl-10" {...register('password')} />
                <button type="button" onClick={() => setShowPass(s => !s)} className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                  {showPass ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
              {errors.password && <p className="text-xs text-destructive">{errors.password.message}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="password_confirmation">{t('auth.confirmPassword')} *</Label>
              <div className="relative">
                <Lock className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input id="password_confirmation" type={showPass ? 'text' : 'password'} className="pr-10 pl-10" {...register('password_confirmation')} />
              </div>
              {errors.password_confirmation && <p className="text-xs text-destructive">{errors.password_confirmation.message}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="batch_id">{t('auth.batch')}</Label>
              <Select value={watch('batch_id')} onValueChange={(v) => setValue('batch_id', v)}>
                <SelectTrigger><SelectValue placeholder={t('auth.selectBatch')} /></SelectTrigger>
                <SelectContent>
                  {batchesData?.data?.map((b: any) => (
                    <SelectItem key={b.id} value={String(b.id)}>{b.code} - {b.name_ar}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="student_id">{t('auth.studentId')}</Label>
              <div className="relative">
                <IdCard className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input id="student_id" className="pr-10" {...register('student_id')} />
              </div>
            </div>
          </div>

          <Button type="submit" disabled={loading} size="lg" className="w-full">
            {loading ? t('common.loading') : t('auth.register')}
          </Button>

          <div className="text-center text-sm">
            {t('auth.alreadyHaveAccount')}{' '}
            <Link to="/auth/login" className="text-primary hover:underline font-medium">{t('auth.login')}</Link>
          </div>
        </form>
      </motion.div>
    </div>
  )
}
