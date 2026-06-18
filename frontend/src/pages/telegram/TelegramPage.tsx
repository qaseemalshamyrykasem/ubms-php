import { useState } from 'react'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { toast } from 'sonner'
import { Send, Link2, Unlink, CheckCircle2, XCircle, ExternalLink, RefreshCw, QrCode } from 'lucide-react'
import { telegramApi } from '@/lib/api-services'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

export default function TelegramPage() {
  const { t } = useTranslation()
  const qc = useQueryClient()
  const [qrUrl, setQrUrl] = useState('')

  const { data: status, isLoading } = useQuery({
    queryKey: ['telegram', 'status'],
    queryFn: () => telegramApi.status().then(r => r.data),
  })

  const generateMutation = useMutation({
    mutationFn: () => telegramApi.generateCode(),
    onSuccess: (r) => {
      const data = r.data
      // Simple QR placeholder
      setQrUrl(`https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=${encodeURIComponent(data.deep_link || data.code)}`)
      toast.success('تم توليد رمز التحقق')
      qc.invalidateQueries({ queryKey: ['telegram', 'status'] })
    },
  })

  const disconnectMutation = useMutation({
    mutationFn: () => telegramApi.disconnect(),
    onSuccess: () => { toast.success(t('telegram.disconnected')); qc.invalidateQueries({ queryKey: ['telegram', 'status'] }) },
  })

  const testMutation = useMutation({
    mutationFn: () => telegramApi.test(),
    onSuccess: () => toast.success('تم إرسال رسالة تجريبية'),
    onError: () => toast.error('فشل الإرسال'),
  })

  if (isLoading) return <div className="skeleton h-96 rounded-xl" />

  const connected = status?.connected
  const botConfigured = status?.bot_configured

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('telegram.title')}</h1>
        <p className="text-muted-foreground">ربط حسابك مع بوت تيليجرام لاستقبال الإشعارات الفورية</p>
      </div>

      {!botConfigured && (
        <Card className="border-amber-300 bg-amber-50/50">
          <CardContent className="p-4 flex items-center gap-3">
            <XCircle className="h-5 w-5 text-amber-500" />
            <p className="text-sm">البوت غير مُعدّ من قِبل الإدارة. تواصل مع المسؤول.</p>
          </CardContent>
        </Card>
      )}

      <div className="grid gap-6 md:grid-cols-2">
        {/* Status card */}
        <Card>
          <CardHeader><CardTitle>حالة الاتصال</CardTitle></CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center gap-3">
              {connected ? (
                <>
                  <CheckCircle2 className="h-10 w-10 text-emerald-500" />
                  <div>
                    <p className="font-bold text-emerald-600">{t('telegram.connected')}</p>
                    <p className="text-xs text-muted-foreground">@{status?.username || '—'}</p>
                  </div>
                </>
              ) : (
                <>
                  <XCircle className="h-10 w-10 text-slate-400" />
                  <div>
                    <p className="font-bold text-slate-500">{t('telegram.notConnected')}</p>
                    <p className="text-xs text-muted-foreground">اضغط على "ربط الحساب" للبدء</p>
                  </div>
                </>
              )}
            </div>

            {connected && (
              <div className="space-y-2 pt-3 border-t">
                <Button variant="outline" className="w-full" onClick={() => testMutation.mutate()} disabled={testMutation.isPending}>
                  <Send className="h-4 w-4" /> {t('telegram.sendTest')}
                </Button>
                <Button variant="destructive" className="w-full" onClick={() => disconnectMutation.mutate()} disabled={disconnectMutation.isPending}>
                  <Unlink className="h-4 w-4" /> {t('telegram.disconnect')}
                </Button>
              </div>
            )}

            {!connected && (
              <Button className="w-full" onClick={() => generateMutation.mutate()} disabled={generateMutation.isPending || !botConfigured}>
                <Link2 className="h-4 w-4" /> {t('telegram.connect')}
              </Button>
            )}
          </CardContent>
        </Card>

        {/* QR / Code display */}
        {generateMutation.data && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2"><QrCode className="h-5 w-5" /> رمز التحقق</CardTitle>
            </CardHeader>
            <CardContent className="text-center space-y-4">
              {qrUrl && <img src={qrUrl} alt="QR" className="mx-auto rounded-lg border" />}
              <div>
                <p className="text-sm text-muted-foreground mb-2">{t('telegram.verificationCodeDesc')}</p>
                <div className="rounded-xl bg-muted p-4 font-mono text-2xl font-bold tracking-wider">
                  {generateMutation.data.data.code}
                </div>
              </div>
              {generateMutation.data.data.deep_link && (
                <a href={generateMutation.data.data.deep_link} target="_blank" rel="noopener noreferrer">
                  <Button className="w-full"><ExternalLink className="h-4 w-4" /> {t('telegram.openTelegram')}</Button>
                </a>
              )}
              <p className="text-xs text-muted-foreground">⏱ تنتهي الصلاحية خلال {generateMutation.data.data.expires_in_minutes} دقائق</p>
              <Button variant="outline" size="sm" onClick={() => generateMutation.mutate()}><RefreshCw className="h-3 w-3" /> توليد جديد</Button>
            </CardContent>
          </Card>
        )}
      </div>

      <Card>
        <CardHeader><CardTitle>كيف يعمل؟</CardTitle></CardHeader>
        <CardContent className="space-y-3 text-sm text-muted-foreground">
          <Step n={1} text="اضغط على زر ربط الحساب" />
          <Step n={2} text="افتح تيليجرام وابدأ محادثة مع البوت" />
          <Step n={3} text="أرسل رمز التحقق المُولّد إلى البوت" />
          <Step n={4} text="سيتم تأكيد الربط واستقبال الإشعارات فوراً" />
        </CardContent>
      </Card>
    </div>
  )
}

function Step({ n, text }: { n: number; text: string }) {
  return (
    <div className="flex items-center gap-3">
      <div className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold">{n}</div>
      <p>{text}</p>
    </div>
  )
}
