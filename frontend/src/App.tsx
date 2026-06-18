import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/store/auth'
import LoginPage from '@/pages/auth/LoginPage'
import RegisterPage from '@/pages/auth/RegisterPage'
import DashboardLayout from '@/components/layout/DashboardLayout'
import DashboardPage from '@/pages/dashboard/DashboardPage'
import AnnouncementsPage from '@/pages/announcements/AnnouncementsPage'
import AttendancePage from '@/pages/attendance/AttendancePage'
import AssignmentsPage from '@/pages/assignments/AssignmentsPage'
import SchedulePage from '@/pages/schedule/SchedulePage'
import CoursesPage from '@/pages/courses/CoursesPage'
import ReportsPage from '@/pages/reports/ReportsPage'
import TelegramPage from '@/pages/telegram/TelegramPage'
import NotificationsPage from '@/pages/notifications/NotificationsPage'
import StudentsPage from '@/pages/students/StudentsPage'
import ProfilePage from '@/pages/profile/ProfilePage'
import BatchesPage from '@/pages/batches/BatchesPage'
import StructurePage from '@/pages/structure/StructurePage'
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage'

function ProtectedRoute({ children, roles }: { children: React.ReactNode; roles?: string[] }) {
  const { isAuthenticated, user } = useAuthStore()
  if (!isAuthenticated) return <Navigate to="/auth/login" replace />
  if (roles && user && !roles.includes(user.role)) return <Navigate to="/dashboard" replace />
  return <>{children}</>
}

function PublicRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore()
  if (isAuthenticated) return <Navigate to="/dashboard" replace />
  return <>{children}</>
}

export default function App() {
  return (
    <Routes>
      <Route path="/auth/login" element={<PublicRoute><LoginPage /></PublicRoute>} />
      <Route path="/auth/register" element={<PublicRoute><RegisterPage /></PublicRoute>} />
      <Route path="/auth/forgot-password" element={<PublicRoute><ForgotPasswordPage /></PublicRoute>} />

      <Route path="/" element={<Navigate to="/dashboard" replace />} />

      <Route path="/dashboard" element={<ProtectedRoute><DashboardLayout><DashboardPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/announcements" element={<ProtectedRoute><DashboardLayout><AnnouncementsPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/attendance" element={<ProtectedRoute><DashboardLayout><AttendancePage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/assignments" element={<ProtectedRoute><DashboardLayout><AssignmentsPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/schedule" element={<ProtectedRoute><DashboardLayout><SchedulePage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/courses" element={<ProtectedRoute roles={['representative', 'college_admin', 'super_admin']}><DashboardLayout><CoursesPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/reports" element={<ProtectedRoute roles={['representative', 'college_admin', 'super_admin']}><DashboardLayout><ReportsPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/telegram" element={<ProtectedRoute><DashboardLayout><TelegramPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/notifications" element={<ProtectedRoute><DashboardLayout><NotificationsPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/students" element={<ProtectedRoute roles={['representative', 'college_admin', 'super_admin']}><DashboardLayout><StudentsPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/profile" element={<ProtectedRoute><DashboardLayout><ProfilePage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/batches" element={<ProtectedRoute roles={['college_admin', 'super_admin']}><DashboardLayout><BatchesPage /></DashboardLayout></ProtectedRoute>} />
      <Route path="/structure" element={<ProtectedRoute roles={['college_admin', 'super_admin']}><DashboardLayout><StructurePage /></DashboardLayout></ProtectedRoute>} />

      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  )
}
