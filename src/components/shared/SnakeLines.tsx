import { useState, useCallback } from 'react'

const SE_ORANGE = '#f79039'
const SE_GOLD = '#febd26'

type Side = 'top' | 'bottom' | 'left' | 'right'
const DIRS: [Side, Side][] = [
  ['bottom', 'right'], ['bottom', 'left'],
  ['top', 'right'], ['top', 'left'],
  ['left', 'bottom'], ['left', 'top'],
  ['right', 'bottom'], ['right', 'top'],
]
const LINE_N = 6
const LINE_GAP = 9
const DASH = 2000

function sidePoint(s: Side) {
  switch (s) {
    case 'top':    return { x: 60 + Math.random() * 980, y: -80 }
    case 'bottom': return { x: 60 + Math.random() * 980, y: 700 }
    case 'left':   return { x: -120, y: 30 + Math.random() * 560 }
    case 'right':  return { x: 1220, y: 30 + Math.random() * 560 }
  }
}

function makePaths(): string[] {
  const [from, to] = DIRS[Math.floor(Math.random() * DIRS.length)]
  const s = sidePoint(from)
  const e = sidePoint(to)
  const dx = e.x - s.x, dy = e.y - s.y
  const len = Math.sqrt(dx * dx + dy * dy) || 1
  const ux = -dy / len, uy = dx / len
  const midPerp = (Math.random() - 0.5) * 80
  const mx = (s.x + e.x) / 2 + ux * midPerp
  const my = (s.y + e.y) / 2 + uy * midPerp
  const sw1 = 40 + Math.random() * 60
  const sw2 = 40 + Math.random() * 60
  const dir1 = Math.random() > 0.5 ? 1 : -1
  const dir2 = -dir1
  const c1x = s.x + (mx - s.x) * 0.35 + ux * sw1 * dir1
  const c1y = s.y + (my - s.y) * 0.35 + uy * sw1 * dir1
  const c2x = s.x + (mx - s.x) * 0.75 + ux * sw1 * dir1 * 0.5
  const c2y = s.y + (my - s.y) * 0.75 + uy * sw1 * dir1 * 0.5
  const c3x = mx + (e.x - mx) * 0.25 + ux * sw2 * dir2 * 0.5
  const c3y = my + (e.y - my) * 0.25 + uy * sw2 * dir2 * 0.5
  const c4x = mx + (e.x - mx) * 0.65 + ux * sw2 * dir2
  const c4y = my + (e.y - my) * 0.65 + uy * sw2 * dir2
  const half = (LINE_N - 1) / 2
  return Array.from({ length: LINE_N }, (_, i) => {
    const t = (i - half) * LINE_GAP
    const ox = ux * t, oy = uy * t
    const f = (v: number) => v.toFixed(1)
    return [
      `M ${f(s.x+ox)} ${f(s.y+oy)}`,
      `C ${f(c1x+ox)} ${f(c1y+oy)}, ${f(c2x+ox)} ${f(c2y+oy)}, ${f(mx+ox)} ${f(my+oy)}`,
      `C ${f(c3x+ox)} ${f(c3y+oy)}, ${f(c4x+ox)} ${f(c4y+oy)}, ${f(e.x+ox)} ${f(e.y+oy)}`,
    ].join(' ')
  })
}

function SnakeInstance({ dur, delay, color }: { dur: number; delay: number; color: string }) {
  const [paths, setPaths] = useState(() => makePaths())
  const regen = useCallback(() => setPaths(makePaths()), [])
  return (
    <>
      {paths.map((d, i) => (
        <path
          key={i} d={d} className="sn" stroke={color}
          strokeDasharray={`${DASH} ${DASH}`}
          style={{ animation: `crawl ${dur}s linear infinite`, animationDelay: `${delay}s` }}
          onAnimationIteration={i === 0 ? regen : undefined}
        />
      ))}
    </>
  )
}

const GRP = [
  { dur: 16, color: SE_ORANGE },
  { dur: 22, color: SE_GOLD },
  { dur: 19, color: SE_ORANGE },
  { dur: 25, color: SE_GOLD },
]

export function SnakeLines() {
  return (
    <svg
      viewBox="0 0 1097 617"
      className="absolute inset-0 w-full h-full pointer-events-none"
      style={{ zIndex: 1 }}
      fill="none"
    >
      <style>{`
        .sn { stroke-linecap: round; stroke-width: 5; stroke-opacity: 0.13; }
        @keyframes crawl { 0% { stroke-dashoffset: ${DASH}; } 100% { stroke-dashoffset: -${DASH}; } }
      `}</style>
      {GRP.map((g, i) => (
        <g key={i}>
          <SnakeInstance dur={g.dur} delay={0} color={g.color} />
          <SnakeInstance dur={g.dur} delay={-(g.dur * 0.7)} color={g.color} />
        </g>
      ))}
    </svg>
  )
}
