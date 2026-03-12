import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { AuthProvider } from '@/providers/AuthProvider'
import { ThemeProvider } from '@/providers/ThemeProvider'
import { QueryProvider } from '@/providers/QueryProvider'
import { KioskLayout } from '@/layouts/KioskLayout'
import { AdminLayout } from '@/layouts/AdminLayout'
import { lazy, Suspense } from 'react'
import { LoadingSpinner } from '@/components/shared/LoadingSpinner'

const WelcomePage = lazy(() => import('@/pages/kiosk/WelcomePage'))
const StatusSelectPage = lazy(() => import('@/pages/kiosk/StatusSelectPage'))
const ServiceSelectPage = lazy(() => import('@/pages/kiosk/ServiceSelectPage'))
const VisitorFormPage = lazy(() => import('@/pages/kiosk/VisitorFormPage'))
const FaceCapturePage = lazy(() => import('@/pages/kiosk/FaceCapturePage'))
const FaceRecognizePage = lazy(() => import('@/pages/kiosk/FaceRecognizePage'))
const TicketPage = lazy(() => import('@/pages/kiosk/TicketPage'))
const EvaluationStandbyPage = lazy(() => import('@/pages/kiosk/EvaluationStandbyPage'))
const EvaluationPage = lazy(() => import('@/pages/kiosk/EvaluationPage'))
const LoginPage = lazy(() => import('@/pages/admin/LoginPage'))
const DashboardPage = lazy(() => import('@/pages/admin/DashboardPage'))
const GuestListPage = lazy(() => import('@/pages/admin/GuestListPage'))
const GuestAddPage = lazy(() => import('@/pages/admin/GuestAddPage'))
const ConsultationQueuePage = lazy(() => import('@/pages/admin/ConsultationQueuePage'))
const ConsultationFormPage = lazy(() => import('@/pages/admin/ConsultationFormPage'))
const VisitLogPage = lazy(() => import('@/pages/admin/VisitLogPage'))
const ManualEntryPage = lazy(() => import('@/pages/admin/ManualEntryPage'))
const NotFoundPage = lazy(() => import('@/pages/NotFoundPage'))

function App() {
  return (
    <BrowserRouter>
      <QueryProvider>
        <ThemeProvider>
          <AuthProvider>
            <Suspense fallback={<LoadingSpinner className="min-h-screen" />}>
              <Routes>
                <Route element={<KioskLayout />}>
                  <Route path="/kiosk" element={<WelcomePage />} />
                  <Route path="/kiosk/status" element={<StatusSelectPage />} />
                  <Route path="/kiosk/service" element={<ServiceSelectPage />} />
                  <Route path="/kiosk/form" element={<VisitorFormPage />} />
                  <Route path="/kiosk/capture" element={<FaceCapturePage />} />
                  <Route path="/kiosk/recognize" element={<FaceRecognizePage />} />
                  <Route path="/kiosk/ticket/:id" element={<TicketPage />} />
                </Route>
                <Route path="/kiosk/evaluasi" element={<EvaluationStandbyPage />} />
                <Route path="/kiosk/evaluasi/:id" element={<EvaluationPage />} />
                <Route path="/login" element={<LoginPage />} />
                <Route element={<AdminLayout />}>
                  <Route path="/admin" element={<DashboardPage />} />
                  <Route path="/admin/guests" element={<GuestListPage />} />
                  <Route path="/admin/guests/add" element={<GuestAddPage />} />
                  <Route path="/admin/consultations" element={<ConsultationQueuePage />} />
                  <Route path="/admin/consultations/:id/form" element={<ConsultationFormPage />} />
                  <Route path="/admin/visits" element={<VisitLogPage />} />
                  <Route path="/admin/manual-entry" element={<ManualEntryPage />} />
                </Route>
                <Route path="/" element={<WelcomePage />} />
                <Route path="*" element={<NotFoundPage />} />
              </Routes>
            </Suspense>
          </AuthProvider>
        </ThemeProvider>
      </QueryProvider>
    </BrowserRouter>
  )
}

export default App
