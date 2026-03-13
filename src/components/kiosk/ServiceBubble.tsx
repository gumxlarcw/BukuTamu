import { CheckCircle } from 'lucide-react'
import type { Service } from '@/api/services'

interface ServiceBubbleProps {
  service: Service
  selected: boolean
  onSelect: () => void
}

export function ServiceBubble({ service, selected, onSelect }: ServiceBubbleProps) {
  return (
    <button
      onClick={onSelect}
      className={`
        relative flex flex-col items-center justify-center gap-3 p-6 rounded-2xl border-2
        transition-all duration-200 transform cursor-pointer text-center w-full min-h-36
        ${selected
          ? 'border-teal-400 bg-teal-500/80 scale-105 shadow-2xl text-white'
          : 'border-white/20 bg-white/10 hover:bg-white/20 hover:border-white/40 text-white hover:scale-102 active:scale-95'
        }
      `}
    >
      {selected && (
        <span className="absolute top-2 right-2">
          <CheckCircle className="w-6 h-6 text-white" />
        </span>
      )}
      <span className="text-3xl" role="img" aria-label={service.name}>
        {service.icon}
      </span>
      <div>
        <p className="font-bold text-base leading-tight">{service.name}</p>
        {service.description && (
          <p className={`text-xs mt-1 ${selected ? 'text-white/80' : 'text-white/60'}`}>
            {service.description}
          </p>
        )}
      </div>
    </button>
  )
}
