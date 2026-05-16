import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './styles/globals.css'

// Capture beforeinstallprompt SECEPATNYA — sebelum React render. Event ini
// fire sekali per page-load oleh Chromium/Edge. Kalau listener dipasang
// belakangan (di hook React), kita kemungkinan besar miss event-nya.
// Hook `useInstallPrompt` membaca dari window.__deferredInstallPrompt.
declare global {
  interface Window {
    __deferredInstallPrompt: Event | null
  }
}
window.__deferredInstallPrompt = null
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault()
  window.__deferredInstallPrompt = e
  window.dispatchEvent(new CustomEvent('pwa-install-available'))
})
window.addEventListener('appinstalled', () => {
  window.__deferredInstallPrompt = null
  window.dispatchEvent(new CustomEvent('pwa-installed'))
})

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)

// Register service worker untuk PWA installability. Hanya di production
// & HTTPS — di dev Vite kelola sendiri tanpa SW agar HMR tidak terganggu.
if ('serviceWorker' in navigator && import.meta.env.PROD) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js', { scope: '/admin' }).catch((err) => {
      console.warn('SW registration failed:', err)
    })
  })
}
