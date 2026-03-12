import { Outlet } from 'react-router-dom'

export function KioskLayout() {
  return (
    <div className="relative min-h-screen bg-black">
      <video
        autoPlay muted loop playsInline
        className="absolute inset-0 w-full h-full object-cover opacity-60"
      >
        <source src="/video/bg-video.mp4" type="video/mp4" />
      </video>
      <div className="relative z-10 flex items-center justify-center min-h-screen p-4">
        <Outlet />
      </div>
    </div>
  )
}
