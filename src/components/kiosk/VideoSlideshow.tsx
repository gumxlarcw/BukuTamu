import { useEffect, useRef, useCallback } from 'react'
import Hls from 'hls.js'

// ─── Playlist (HLS streams — .ts segments bypass CF throttle) ─
const PLAYLIST: { hls: string; portrait: boolean }[] = [
  { hls: '/hls/30 Detik SE2026/index.m3u8', portrait: false },
  { hls: '/hls/Billboard SE2026 - 1920x1080/index.m3u8', portrait: false },
  { hls: '/hls/SE2026 - Motion Logo/index.m3u8', portrait: false },
  { hls: '/hls/SE2026 - Motion Explainer/index.m3u8', portrait: false },
  { hls: '/hls/BPS - ILM STORY 1/index.m3u8', portrait: true },
  { hls: '/hls/BPS - ILM STORY 4/index.m3u8', portrait: true },
  { hls: '/hls/BPS - ILM STORY 5/index.m3u8', portrait: true },
  { hls: '/hls/[Sensus Ekonomi] Mencatat Indonesia - Official Theme Song Sensus Ekonomi 2026 (SE2026) (1)/index.m3u8', portrait: false },
]

const FADE_MS = 500

export default function VideoSlideshow() {
  const wrapRef = useRef<HTMLDivElement>(null)
  const vidA = useRef<HTMLVideoElement>(null)
  const vidB = useRef<HTMLVideoElement>(null)
  const canvasA = useRef<HTMLCanvasElement>(null)
  const canvasB = useRef<HTMLCanvasElement>(null)
  const spinnerRef = useRef<HTMLDivElement>(null)
  const dotsRef = useRef<(HTMLDivElement | null)[]>([])

  // HLS instances for each slot
  const hlsA = useRef<Hls | null>(null)
  const hlsB = useRef<Hls | null>(null)

  const s = useRef({
    idx: 0,
    slot: 'A' as 'A' | 'B',
    busy: false,
    preloaded: false,
  })

  const getVid = useCallback((slot: 'A' | 'B') => (slot === 'A' ? vidA : vidB).current!, [])
  const getCanvas = useCallback((slot: 'A' | 'B') => (slot === 'A' ? canvasA : canvasB).current!, [])
  const getHls = useCallback((slot: 'A' | 'B') => slot === 'A' ? hlsA : hlsB, [])
  const slotEl = useCallback((slot: 'A' | 'B') =>
    (slot === 'A' ? vidA : vidB).current!.parentElement as HTMLElement, [])

  const drawBlur = useCallback((slot: 'A' | 'B') => {
    const v = getVid(slot)
    const c = getCanvas(slot)
    if (!v || !c || v.videoWidth === 0) return
    const ctx = c.getContext('2d')
    if (!ctx) return
    c.width = 64
    c.height = 36
    ctx.drawImage(v, 0, 0, 64, 36)
  }, [getVid, getCanvas])

  // ── Attach HLS to a video element ──
  const attachHls = useCallback((slot: 'A' | 'B', idx: number): Promise<void> => {
    const v = getVid(slot)
    const hlsRef = getHls(slot)

    // Destroy old instance
    if (hlsRef.current) {
      hlsRef.current.destroy()
      hlsRef.current = null
    }

    const entry = PLAYLIST[idx]

    return new Promise((resolve) => {
      if (Hls.isSupported()) {
        const hls = new Hls({
          maxBufferLength: 10,
          maxMaxBufferLength: 30,
          startLevel: -1,
        })
        hlsRef.current = hls

        hls.loadSource(entry.hls)
        hls.attachMedia(v)

        hls.on(Hls.Events.MANIFEST_PARSED, () => {
          resolve()
        })

        hls.on(Hls.Events.ERROR, (_event, data) => {
          if (data.fatal) {
            console.error('HLS fatal error:', data.type, data.details)
            resolve() // Don't block slideshow
          }
        })

        // Safety timeout
        setTimeout(resolve, 8000)
      } else if (v.canPlayType('application/vnd.apple.mpegurl')) {
        // Native HLS (Safari)
        v.src = entry.hls
        v.addEventListener('loadedmetadata', () => resolve(), { once: true })
        setTimeout(resolve, 8000)
      } else {
        resolve()
      }
    })
  }, [getVid, getHls])

  // ── Preload next ──
  const preloadNext = useCallback(() => {
    if (s.current.preloaded) return
    s.current.preloaded = true

    const nextIdx = (s.current.idx + 1) % PLAYLIST.length
    const nextSlot = s.current.slot === 'A' ? 'B' : 'A'
    attachHls(nextSlot, nextIdx)
  }, [attachHls])

  // ── Advance ──
  const advance = useCallback(async () => {
    if (s.current.busy) return
    s.current.busy = true

    const nextIdx = (s.current.idx + 1) % PLAYLIST.length
    const curSlot = s.current.slot
    const nextSlot = curSlot === 'A' ? 'B' : 'A'
    const nextVid = getVid(nextSlot)
    const curVid = getVid(curSlot)
    const entry = PLAYLIST[nextIdx]

    // Ensure HLS is attached
    if (!s.current.preloaded) {
      if (spinnerRef.current) spinnerRef.current.style.opacity = '1'
      await attachHls(nextSlot, nextIdx)
    }

    if (spinnerRef.current) spinnerRef.current.style.opacity = '0'

    nextVid.currentTime = 0
    await nextVid.play().catch(() => {})

    if (entry.portrait) {
      await new Promise(r => requestAnimationFrame(r))
      drawBlur(nextSlot)
    }

    // Orientation
    const wrap = wrapRef.current!
    wrap.classList.toggle('portrait', entry.portrait)
    wrap.classList.toggle('landscape', !entry.portrait)

    // Crossfade
    const nextEl = slotEl(nextSlot)
    const curEl = slotEl(curSlot)
    nextEl.classList.add('visible')
    nextEl.classList.remove('hidden')
    curEl.classList.add('hidden')
    curEl.classList.remove('visible')

    setTimeout(() => { curVid.pause() }, FADE_MS + 50)

    s.current.idx = nextIdx
    s.current.slot = nextSlot
    s.current.preloaded = false
    s.current.busy = false

    dotsRef.current.forEach((d, i) => d?.classList.toggle('active', i === nextIdx))
  }, [attachHls, getVid, slotEl, drawBlur])

  // ── Preload next while playing ──
  useEffect(() => {
    const onTime = () => {
      const v = getVid(s.current.slot)
      if (v.currentTime > 2) preloadNext()
    }
    const a = vidA.current!
    const b = vidB.current!
    a.addEventListener('timeupdate', onTime)
    b.addEventListener('timeupdate', onTime)
    return () => {
      a.removeEventListener('timeupdate', onTime)
      b.removeEventListener('timeupdate', onTime)
    }
  }, [getVid, preloadNext])

  // ── Auto-advance on ended ──
  useEffect(() => {
    const onEnd = () => advance()
    const a = vidA.current!
    const b = vidB.current!
    a.addEventListener('ended', onEnd)
    b.addEventListener('ended', onEnd)
    return () => {
      a.removeEventListener('ended', onEnd)
      b.removeEventListener('ended', onEnd)
    }
  }, [advance])

  // ── Bootstrap ──
  useEffect(() => {
    attachHls('A', 0).then(() => {
      const v = vidA.current!
      v.play().catch(() => {})
      if (spinnerRef.current) spinnerRef.current.style.opacity = '0'
      if (PLAYLIST[0].portrait) {
        requestAnimationFrame(() => drawBlur('A'))
      }
    })

    return () => {
      hlsA.current?.destroy()
      hlsB.current?.destroy()
    }
  }, [attachHls, drawBlur])

  return (
    <>
      <style>{`
        .vslide-wrap {
          position: relative;
          width: 100%;
          aspect-ratio: 16/9;
          overflow: hidden;
          background: #0a0a0a;
          border-radius: 14px 14px 0 0;
        }
        .vslide-slot {
          position: absolute; inset: 0; z-index: 1;
          transition: opacity ${FADE_MS}ms ease-in-out;
        }
        .vslide-slot.hidden { opacity: 0; }
        .vslide-slot.visible { opacity: 1; }
        .vslide-slot video { width: 100%; height: 100%; }

        .vslide-wrap.landscape .vslide-slot video { object-fit: cover; }
        .vslide-wrap.landscape .vslide-blur-canvas { display: none; }

        .vslide-wrap.portrait .vslide-slot {
          display: flex; align-items: center; justify-content: center;
        }
        .vslide-wrap.portrait .vslide-slot video {
          position: relative; z-index: 2;
          height: 100%; width: auto; object-fit: contain;
        }
        .vslide-blur-canvas {
          position: absolute; inset: -20px; z-index: 1;
          width: calc(100% + 40px); height: calc(100% + 40px);
          object-fit: cover;
          filter: blur(20px) brightness(0.4) saturate(1.4);
          transform: scale(1.15);
        }

        .vslide-spinner {
          position: absolute; inset: 0; z-index: 15;
          display: flex; align-items: center; justify-content: center;
          opacity: 1; transition: opacity 0.3s ease;
          pointer-events: none;
        }
        .vslide-spinner::after {
          content: ''; width: 32px; height: 32px;
          border: 3px solid rgba(255,255,255,0.15);
          border-top-color: rgba(255,255,255,0.8);
          border-radius: 50%;
          animation: vspin 0.7s linear infinite;
        }
        @keyframes vspin { to { transform: rotate(360deg); } }

        .vslide-dots {
          position: absolute; bottom: 10px; left: 50%;
          transform: translateX(-50%);
          z-index: 20; display: flex; gap: 5px;
          pointer-events: none;
        }
        .vslide-dot {
          width: 6px; height: 6px; border-radius: 50%;
          background: rgba(255,255,255,0.35);
          transition: all 0.3s ease;
          box-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        .vslide-dot.active {
          background: rgba(255,255,255,0.95);
          width: 18px; border-radius: 3px;
        }
      `}</style>

      <div ref={wrapRef} className="vslide-wrap landscape">
        <div className="vslide-slot visible">
          <canvas ref={canvasA} className="vslide-blur-canvas" />
          <video ref={vidA} muted playsInline />
        </div>
        <div className="vslide-slot hidden">
          <canvas ref={canvasB} className="vslide-blur-canvas" />
          <video ref={vidB} muted playsInline />
        </div>
        <div ref={spinnerRef} className="vslide-spinner" />
        <div className="vslide-dots">
          {PLAYLIST.map((_, i) => (
            <div key={i} ref={el => { dotsRef.current[i] = el }} className={`vslide-dot${i === 0 ? ' active' : ''}`} />
          ))}
        </div>
      </div>
    </>
  )
}
