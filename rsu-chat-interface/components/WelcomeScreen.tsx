import {
  Stethoscope,
  CalendarPlus,
  FileText,
  Building,
  Clock,
  BedDouble,
  Info,
} from "lucide-react";
import Image from "next/image";

interface WelcomeScreenProps {
  onSuggestionClick: (suggestion: string) => void;
  isGuest?: boolean;
  onOpenAuth: (mode: "login" | "register") => void; 
}

export default function WelcomeScreen({
  onSuggestionClick,
  isGuest = true,
  onOpenAuth,
}: WelcomeScreenProps) {
  const suggestions = [
    { text: "Bagaimana cara daftar online?", icon: CalendarPlus },
    { text: "Dokter spesialis apa yang tersedia?", icon: Stethoscope },
    { text: "Bagaimana booking antrean BPJS?", icon: FileText },
    { text: "Apa saja fasilitas RSU Aulia?", icon: Building },
    { text: "Berapa jam besuk pasien?", icon: Clock },
    { text: "Apa saja kelas kamar rawat inap?", icon: BedDouble },
  ];

  return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] text-center px-4 fade-in">
      <Image
        src="/logo.png"
        alt="Logo RSU Aulia"
        width={140}
        height={140}
        className="object-contain mb-4 drop-shadow-sm"
      />
      <h2 className="text-2xl md:text-3xl font-bold text-gray-800 mb-3 tracking-tight">
        Selamat Datang di RSU Aulia
      </h2>
      <p className="text-gray-500 max-w-lg mb-6 text-sm md:text-base leading-relaxed">
        Asisten virtual Anda siap membantu 24/7. Tanyakan apa saja seputar
        layanan kesehatan dan jadwal dokter kami.
      </p>

      {isGuest && (
        <div className="mb-8 p-4 bg-green-50 border border-slate-200 rounded-2xl max-w-3xl w-full flex items-start gap-4 text-left shadow-sm">
          <div className="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center shrink-0 mt-0.5">
            <Info className="w-5 h-5 text-slate-600" />
          </div>
          <div>
            <h3 className="text-sm font-semibold text-slate-800 mb-1">
              Informasi Penggunaan (Mode Guest)
            </h3>
            <p className="text-sm text-slate-600 leading-relaxed">
              Anda memiliki batas maksimal <strong>3 kali pertanyaan</strong>.
              Silakan manfaatkan kuota ini untuk mencari informasi prioritas
              Anda, atau {/* --- UBAH JADI BUTTON DI SINI --- */}
              <button
                onClick={() => onOpenAuth("login")}
                className="text-emerald-600 hover:text-emerald-700 hover:underline font-semibold transition-colors"
              >
                Masuk / Daftar Akun
              </button>{" "}
              untuk akses tanya jawab tanpa batas.
            </p>
          </div>
        </div>
      )}

      {/* (Bagian mapping suggestions tetap sama persis) */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 w-full max-w-3xl">
        {suggestions.map((suggestion, index) => {
          const Icon = suggestion.icon;
          return (
            <button
              key={index}
              onClick={() => onSuggestionClick(suggestion.text)}
              className="flex items-center gap-4 p-4 bg-white hover:bg-emerald-50 border border-gray-200 hover:border-emerald-300 rounded-2xl text-left transition-all duration-300 shadow-sm hover:shadow-md group"
            >
              <div className="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center shrink-0 border border-emerald-100 group-hover:bg-emerald-200 transition-colors duration-300">
                <Icon className="w-5 h-5 text-emerald-600" />
              </div>
              <span className="text-sm font-medium text-gray-700 group-hover:text-emerald-900 transition-colors">
                {suggestion.text}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
}
