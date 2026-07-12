import { Menu, LogOut } from "lucide-react";
import Image from "next/image";

interface ChatHeaderProps {
  user: any;
  isSidebarOpen: boolean;
  setIsSidebarOpen: (isOpen: boolean) => void;
  onLogout: () => void;
  onOpenAuth: (mode: "login" | "register") => void;
}

export default function ChatHeader({
  user,
  isSidebarOpen,
  setIsSidebarOpen,
  onLogout,
  onOpenAuth,
}: ChatHeaderProps) {
  return (
    <header className="bg-white/80 backdrop-blur-md border-b border-gray-100 p-4 flex items-center justify-between absolute top-0 w-full z-10">
      <div className="flex items-center gap-3">
        <button
          onClick={() => setIsSidebarOpen(!isSidebarOpen)}
          className="p-2 hover:bg-gray-100 rounded-lg text-gray-600"
        >
          <Menu className="w-6 h-6" />
        </button>
        <div className="flex items-center gap-2">
          <Image
            src="/logo-rs-aulia.png"
            alt="Logo RSU Aulia"
            width={32}
            height={32}
            className="object-contain"
          />
          <h1 className="font-bold text-lg text-gray-800">RSU Aulia AI</h1>
        </div>
      </div>

      <div>
        {user ? (
          <div className="flex items-center gap-3">
            <span className="text-sm font-medium text-gray-700 hidden md:block">
              Halo, {user.name}
            </span>
            <button
              onClick={onLogout}
              className="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg flex items-center gap-2 text-sm font-semibold transition-colors"
            >
              <LogOut className="w-4 h-4" /> Keluar
            </button>
          </div>
        ) : (
          <div className="flex items-center gap-2">
            <button
              onClick={() => onOpenAuth("login")}
              className="px-4 py-2 text-emerald-700 hover:bg-emerald-50 font-semibold rounded-lg text-sm transition-colors"
            >
              Masuk
            </button>
            <button
              onClick={() => onOpenAuth("register")}
              className="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 font-semibold rounded-lg text-sm transition-colors shadow-sm"
            >
              Daftar
            </button>
          </div>
        )}
      </div>
    </header>
  );
}
