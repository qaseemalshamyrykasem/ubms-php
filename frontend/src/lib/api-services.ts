import api from './api'
import type {
  Announcement, Assignment, Attendance, Batch, Course, Lecture,
  DashboardStats, SiteNotification, TelegramStatus, TelegramCodeResponse,
  User, Schedule, AuditLog,
} from '@/types'

export const authApi = {
  login: (email: string, password: string, remember = false) =>
    api.post<{ user: User; token: string; message: string }>('/auth/login', { email, password, remember }),
  register: (data: Record<string, unknown>) =>
    api.post<{ user: User; token: string; message: string }>('/auth/register', data),
  me: () => api.get<{ user: User }>('/auth/me'),
  logout: () => api.post('/auth/logout'),
  forgotPassword: (email: string) => api.post('/auth/forgot-password', { email }),
  resetPassword: (data: Record<string, unknown>) => api.post('/auth/reset-password', data),
  updateProfile: (data: FormData | Record<string, unknown>) =>
    api.put<{ user: User }>('/auth/profile', data, data instanceof FormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : undefined),
  changePassword: (data: { current_password: string; password: string; password_confirmation: string }) =>
    api.put('/auth/password', data),
}

export const dashboardApi = {
  stats: () => api.get<DashboardStats>('/dashboard/stats'),
  search: (q: string) => api.get('/search', { params: { q } }),
}

export const structureApi = {
  hierarchy: () => api.get('/structure/hierarchy'),
  universities: () => api.get('/universities'),
  colleges: () => api.get('/colleges'),
  departments: (params?: Record<string, unknown>) => api.get('/departments', { params }),
  levels: (params?: Record<string, unknown>) => api.get('/levels', { params }),
  sections: (params?: Record<string, unknown>) => api.get('/sections', { params }),
  batches: (params?: Record<string, unknown>) => api.get('/batches', { params }),
  createBatch: (data: Record<string, unknown>) => api.post('/batches', data),
  showBatch: (id: number) => api.get(`/batches/${id}`),
  batchStudents: (id: number) => api.get(`/batches/${id}/students`),
}

export const announcementApi = {
  list: (params?: Record<string, unknown>) => api.get('/announcements', { params }),
  show: (id: number) => api.get(`/announcements/${id}`),
  create: (data: FormData) => api.post('/announcements', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  update: (id: number, data: FormData) => api.post(`/announcements/${id}`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  delete: (id: number) => api.delete(`/announcements/${id}`),
  togglePin: (id: number) => api.post(`/announcements/${id}/pin`),
  stats: (id: number) => api.get(`/announcements/${id}/stats`),
}

export const attendanceApi = {
  lectures: (params?: Record<string, unknown>) => api.get('/attendance/lectures', { params }),
  createLecture: (data: Record<string, unknown>) => api.post('/attendance/lectures', data),
  refreshQr: (id: number) => api.post(`/attendance/lectures/${id}/refresh-qr`),
  qrCode: (id: number) => api.get(`/attendance/lectures/${id}/qr`, { responseType: 'blob' }),
  submit: (id: number, records: Array<{ student_id: number; status: string; notes?: string }>) =>
    api.post(`/attendance/lectures/${id}/submit`, { records }),
  lock: (id: number) => api.post(`/attendance/lectures/${id}/lock`),
  scan: (lecture_id: number, token: string) => api.post('/attendance/scan', { lecture_id, token }),
  myStats: () => api.get('/attendance/my-stats'),
  myHistory: (params?: Record<string, unknown>) => api.get('/attendance/my-history', { params }),
  batchStats: () => api.get('/attendance/batch-stats'),
}

export const assignmentApi = {
  list: (params?: Record<string, unknown>) => api.get('/assignments', { params }),
  show: (id: number) => api.get(`/assignments/${id}`),
  create: (data: FormData) => api.post('/assignments', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  update: (id: number, data: FormData) => api.post(`/assignments/${id}`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  delete: (id: number) => api.delete(`/assignments/${id}`),
  download: (assignmentId: number, attachmentId: number) =>
    api.get(`/assignments/${assignmentId}/attachments/${attachmentId}/download`, { responseType: 'blob' }),
}

export const courseApi = {
  list: (params?: Record<string, unknown>) => api.get('/courses', { params }),
  show: (id: number) => api.get(`/courses/${id}`),
  create: (data: Record<string, unknown>) => api.post('/courses', data),
  update: (id: number, data: Record<string, unknown>) => api.put(`/courses/${id}`, data),
  delete: (id: number) => api.delete(`/courses/${id}`),
  uploadFile: (id: number, data: FormData) =>
    api.post(`/courses/${id}/files`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
}

export const scheduleApi = {
  list: () => api.get('/schedules'),
  create: (data: Record<string, unknown>) => api.post('/schedules', data),
  update: (id: number, data: Record<string, unknown>) => api.put(`/schedules/${id}`, data),
  delete: (id: number) => api.delete(`/schedules/${id}`),
}

export const notificationApi = {
  list: (params?: Record<string, unknown>) => api.get('/notifications', { params }),
  unreadCount: () => api.get<{ count: number }>('/notifications/unread-count'),
  markRead: (id: number) => api.post(`/notifications/${id}/read`),
  markAllRead: () => api.post('/notifications/read-all'),
  delete: (id: number) => api.delete(`/notifications/${id}`),
  clearAll: () => api.delete('/notifications'),
}

export const telegramApi = {
  status: () => api.get<TelegramStatus>('/telegram/status'),
  generateCode: () => api.post<TelegramCodeResponse>('/telegram/generate-code'),
  disconnect: () => api.post('/telegram/disconnect'),
  test: () => api.post('/telegram/test'),
}

export const reportApi = {
  attendance: (batchId: number, format: 'excel' | 'pdf', params?: Record<string, unknown>) =>
    api.get(`/reports/attendance/${batchId}/${format}`, { params, responseType: 'blob' }),
  students: (batchId: number) => api.get(`/reports/students/${batchId}`, { responseType: 'blob' }),
  announcements: (batchId: number) => api.get(`/reports/announcements/${batchId}`, { responseType: 'blob' }),
  assignments: (batchId: number) => api.get(`/reports/assignments/${batchId}`, { responseType: 'blob' }),
  statistics: (batchId: number) => api.get(`/reports/statistics/${batchId}/excel`, { responseType: 'blob' }),
  stats: (batchId: number) => api.get(`/reports/stats/${batchId}`),
}

export type {
  Announcement, Assignment, Attendance, Batch, Course, Lecture,
  DashboardStats, SiteNotification, TelegramStatus, TelegramCodeResponse,
  User, Schedule, AuditLog,
}
