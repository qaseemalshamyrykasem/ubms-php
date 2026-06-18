// Authentication & User
export interface User {
  id: number
  name: string
  name_ar?: string
  email: string
  phone?: string
  avatar?: string
  role: 'super_admin' | 'college_admin' | 'representative' | 'student'
  status: 'active' | 'inactive' | 'suspended'
  locale: 'ar' | 'en'
  dark_mode: boolean
  timezone: string
  telegram_connected: boolean
  email_verified: boolean
  created_at: string
  student?: StudentInfo
  representative?: RepInfo
  college_admin?: CollegeAdminInfo
}

export interface StudentInfo {
  id: number
  student_id: string
  batch?: BatchInfo
}

export interface RepInfo {
  id: number
  batch?: BatchInfo
}

export interface CollegeAdminInfo {
  college: { id: number; name_ar: string }
}

export interface BatchInfo {
  id: number
  code: string
  name_ar: string
  chain?: string
}

// University structure
export interface University { id: number; name: string; name_ar: string; code: string; logo?: string }
export interface College { id: number; name: string; name_ar: string; code: string; university_id: number }
export interface Department { id: number; name: string; name_ar: string; code: string; college_id: number }
export interface Level { id: number; name: string; name_ar: string; level_number: number; department_id: number }
export interface Section { id: number; name: string; name_ar: string; code?: string; level_id: number; capacity: number }
export interface Batch {
  id: number
  name: string
  name_ar: string
  code: string
  start_year: number
  end_year?: number
  start_date?: string
  end_date?: string
  is_active: boolean
  section?: Section
}

// Announcements
export type AnnouncementType =
  | 'holiday' | 'assignment' | 'lecture' | 'schedule'
  | 'general' | 'urgent' | 'emergency' | 'meeting' | 'important'

export interface AnnouncementAttachment {
  id: number
  file_path: string
  original_name: string
  file_type: string
  file_size: number
}

export interface Announcement {
  id: number
  batch_id: number
  user_id: number
  course_id?: number
  title: string
  body: string
  type: AnnouncementType
  is_pinned: boolean
  is_published: boolean
  scheduled_at?: string
  published_at?: string
  expires_at?: string
  send_telegram: boolean
  telegram_sent: boolean
  created_at: string
  updated_at: string
  author?: { id: number; name: string; name_ar?: string }
  course?: Course
  attachments?: AnnouncementAttachment[]
  reads_count?: number
}

export interface Course {
  id: number
  name: string
  name_ar: string
  code: string
  description?: string
  credit_hours: number
  instructor_name?: string
  is_active: boolean
  department_id: number
}

// Attendance
export type AttendanceStatus = 'present' | 'absent' | 'late' | 'excused'

export interface Lecture {
  id: number
  batch_id: number
  course_id: number
  title?: string
  date: string
  start_time: string
  end_time?: string
  room?: string
  qr_token: string
  qr_expires_at?: string
  attendance_locked: boolean
  course?: Course
  attendances?: Attendance[]
}

export interface Attendance {
  id: number
  lecture_id: number
  student_id: number
  status: AttendanceStatus
  verification_method: string
  recorded_at?: string
  notes?: string
  lecture?: Lecture
  student?: User
}

// Assignments
export interface AssignmentAttachment {
  id: number
  file_path: string
  original_name: string
  file_type: string
  file_size: number
}

export interface Assignment {
  id: number
  batch_id: number
  course_id?: number
  user_id: number
  title: string
  description?: string
  deadline: string
  max_grade: number
  allow_late_submission: boolean
  late_penalty_percent: number
  notify_telegram: boolean
  created_at: string
  course?: Course
  author?: { id: number; name: string; name_ar?: string }
  attachments?: AssignmentAttachment[]
}

// Schedules
export type DayName = 'sunday' | 'monday' | 'tuesday' | 'wednesday' | 'thursday' | 'friday' | 'saturday'

export interface Schedule {
  id: number
  batch_id: number
  course_id: number
  day: DayName
  start_time: string
  end_time: string
  room?: string
  building?: string
  instructor_name?: string
  notes?: string
  is_recurring: boolean
  effective_from?: string
  effective_until?: string
  course?: Course
}

// Notifications
export interface SiteNotification {
  id: number
  user_id: number
  title: string
  body: string
  type: 'info' | 'success' | 'warning' | 'danger'
  link?: string
  is_read: boolean
  read_at?: string
  scheduled_at?: string
  created_at: string
}

// Dashboard
export interface DashboardStats {
  role: string
  batch?: BatchInfo
  college?: { id: number; name_ar: string }
  stats: Record<string, number>
  daily_activity?: Record<string, number>
  announcements_by_type?: Record<string, number>
  recent_activity?: AuditLog[]
  recent_announcements?: Announcement[]
  upcoming_assignments?: Assignment[]
  attendance_stats?: AttendanceStats
}

export interface AttendanceStats {
  total_lectures: number
  present: number
  late: number
  absent: number
  excused: number
  rate: number
  by_course?: Array<{ course: string; total: number; present: number; absent: number; rate: number }>
}

export interface AuditLog {
  id: number
  action: string
  resource_type?: string
  resource_id?: number
  ip_address?: string
  created_at: string
}

// Telegram
export interface TelegramStatus {
  connected: boolean
  username?: string
  connected_at?: string
  bot_configured: boolean
  bot_username?: string
}

export interface TelegramCodeResponse {
  code: string
  deep_link?: string
  bot_username?: string
  expires_in_minutes: number
}
