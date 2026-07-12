import { Plus, MessageSquare, Info, Trash2 } from "lucide-react";

interface SidebarProps {
  isOpen: boolean;
  onNewChat: () => void;
  sessions: any[];
  onSelectSession: (id: number) => void;
  onDeleteSession: (id: number) => void;
  currentSessionId: number | null;
  isLoggedIn: boolean;
}

export default function Sidebar({
  isOpen,
  onNewChat,
  sessions,
  onSelectSession,
  onDeleteSession,
  currentSessionId,
  isLoggedIn,
}: SidebarProps) {
  return (
    <aside
      className={`${isOpen ? "w-64" : "w-0"} transition-all duration-300 ease-in-out bg-emerald-900 text-white flex flex-col shrink-0 overflow-hidden`}
    >
      <div className="p-4">
        <button
          onClick={onNewChat}
          className="flex items-center gap-2 w-full bg-emerald-800 hover:bg-emerald-700 transition-colors p-3 rounded-xl font-medium border border-emerald-700/50"
        >
          <Plus className="w-5 h-5" />
          Percakapan Baru
        </button>
      </div>

      <div className="flex-1 overflow-y-auto p-4 space-y-2">
        <p className="text-xs font-semibold text-emerald-400 mb-2 px-2 tracking-wider">
          RIWAYAT CHAT
        </p>

        {!isLoggedIn ? (
          <div className="px-2 text-sm text-emerald-300/70 italic text-center mt-4">
            Login untuk menyimpan dan melihat riwayat percakapan.
          </div>
        ) : sessions.length === 0 ? (
          <div className="px-2 text-sm text-emerald-300/70 italic text-center mt-4">
            Belum ada riwayat.
          </div>
        ) : (
          sessions.map((session) => (
            <div
              key={session.id}
              className={`flex items-center justify-between w-full p-2 rounded-lg transition-colors border ${
                currentSessionId === session.id
                  ? "bg-emerald-700 border-emerald-500 text-white"
                  : "bg-transparent border-transparent hover:bg-emerald-800/50 text-emerald-100"
              }`}
            >
              <button
                onClick={() => onSelectSession(session.id)}
                className="flex items-center gap-3 flex-1 text-left text-sm truncate pr-2"
              >
                <MessageSquare className="w-4 h-4 shrink-0" />
                <span className="truncate">{session.title}</span>
              </button>

              {/* PERUBAHAN DI SINI: opacity-0 dan group-hover:opacity-100 Dihapus */}
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  onDeleteSession(session.id);
                }}
                className="p-1.5 text-emerald-400 hover:text-red-400 hover:bg-emerald-800 rounded-md transition-all shrink-0"
                title="Hapus Percakapan"
              >
                <Trash2 className="w-4 h-4" />
              </button>
            </div>
          ))
        )}
      </div>

      <div className="p-4 border-t border-emerald-800/50">
        <div className="flex items-center gap-3 text-sm text-emerald-200 bg-emerald-800/30 p-3 rounded-lg">
          <Info className="w-5 h-5 shrink-0" />
          <p className="text-[10px] leading-tight">
            Sistem dalam tahap uji coba (Beta). Verifikasi medis ke dokter.
          </p>
        </div>
      </div>
    </aside>
  );
}
