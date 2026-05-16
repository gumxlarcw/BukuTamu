import { CheckCircle, BookOpen, BarChart3, ClipboardList, ShoppingCart, User, MoreHorizontal, type LucideIcon } from 'lucide-react'
import type { Service } from '@/api/services'

const iconMap: Record<string, LucideIcon> = {
  book: BookOpen,
  chart: BarChart3,
  clipboard: ClipboardList,
  'shopping-cart': ShoppingCart,
  user: User,
  more: MoreHorizontal,
}

interface ServiceBubbleProps {
  service: Service
  selected: boolean
  onSelect: () => void
}

export function ServiceBubble({ service, selected, onSelect }: ServiceBubbleProps) {
  const Icon = iconMap[service.icon] || MoreHorizontal

  return (
    <button
      onClick={onSelect}
      className={`
        relative flex flex-col items-center justify-center gap-2 p-4 rounded-2xl border-2 overflow-hidden
        transition-all duration-200 transform cursor-pointer text-center w-full
        ${selected
          ? 'border-orange-400 bg-orange-500 scale-105 shadow-2xl text-white'
          : 'border-gray-200 bg-white/70 hover:bg-white/90 hover:border-gray-300 text-gray-800 hover:scale-[1.02] active:scale-95 shadow-md'
        }
      `}
    >
      {selected && (
        <span className="absolute top-2 right-2">
          <CheckCircle className="w-6 h-6 text-white" />
        </span>
      )}
      <Icon className="w-8 h-8 shrink-0" />
      <div className="w-full">
        <p className="font-bold text-xs leading-snug break-words">{service.name}</p>
        {service.description && (
          <p className={`text-[10px] mt-0.5 leading-snug break-words ${selected ? 'text-white/80' : 'text-gray-500'}`}>
            {service.description}
          </p>
        )}
      </div>
    </button>
  )
}
