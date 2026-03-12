import { Link } from 'react-router-dom'
import { buttonVariants } from '@/components/ui/button'

export default function NotFoundPage() {
  return (
    <div className="flex flex-col items-center justify-center min-h-screen gap-4">
      <h1 className="text-6xl font-bold text-muted-foreground">404</h1>
      <p className="text-lg text-muted-foreground">Halaman tidak ditemukan</p>
      <Link to="/" className={buttonVariants()}>Kembali</Link>
    </div>
  )
}
