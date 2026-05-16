import { Outlet } from 'react-router-dom'
import { useEffect } from 'react'
import { preloadFaceModels } from '@/lib/face-detection'
import { SnakeLines } from '@/components/shared/SnakeLines'

export function KioskLayout() {
  useEffect(() => { preloadFaceModels() }, [])
  return (
    <div
      className="relative overflow-hidden"
      style={{
        width: 'calc(100vw / 1.75)',
        height: 'calc(100vh / 1.75)',
        zoom: 1.75,
        border: 'none',
        fontFamily: "'Outfit', system-ui, sans-serif",
      }}
    >
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        .kiosk-bg {
          font-family: 'Outfit', system-ui, sans-serif;
          background: linear-gradient(
            135deg,
            #f8f5f0 0%,
            #fef3ec 25%,
            #f0f4f8 50%,
            #fdf6ee 75%,
            #f8f5f0 100%
          );
          background-size: 400% 400%;
          animation: gradientShift 15s ease infinite;
        }
        @keyframes gradientShift {
          0% { background-position: 0% 50%; }
          25% { background-position: 100% 0%; }
          50% { background-position: 100% 100%; }
          75% { background-position: 0% 100%; }
          100% { background-position: 0% 50%; }
        }
        .kiosk-enter {
          opacity: 0;
          transform: translateY(20px);
          animation: kioskFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes kioskFadeUp {
          to { opacity: 1; transform: translateY(0); }
        }
        .kiosk-scroll { overflow-y: auto; }
        .kiosk-scroll::-webkit-scrollbar { display: none; }
        .kiosk-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .kiosk-bg ~ div * { overflow-wrap: break-word; word-break: break-word; }
      `}</style>

      <div className="kiosk-bg absolute inset-0" />

      <SnakeLines />

      <div className="relative z-10 h-full p-4 flex flex-col">
        <div className="flex-1 min-h-0 flex items-center justify-center">
          <Outlet />
        </div>
      </div>
    </div>
  )
}
