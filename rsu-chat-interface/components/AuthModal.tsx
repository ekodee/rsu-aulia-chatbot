import { useState } from "react";
import { X, Loader2 } from "lucide-react";

interface AuthModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (user: any, token: string) => void;
  customMessage?: string;
  defaultIsLogin?: boolean;
}

export default function AuthModal({
  isOpen,
  onClose,
  onSuccess,
  customMessage,
  defaultIsLogin = true,
}: AuthModalProps) {
  const [isLogin, setIsLogin] = useState(defaultIsLogin);
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");

  const [prevIsOpen, setPrevIsOpen] = useState(isOpen);
  if (isOpen !== prevIsOpen) {
    setPrevIsOpen(isOpen);
    if (isOpen) {
      setIsLogin(defaultIsLogin);
      setName("");
      setEmail("");
      setPassword("");
      setPasswordConfirmation("");
      setError("");
    }
  }

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError("");

    // Validasi konfirmasi password untuk register
    if (!isLogin && password !== passwordConfirmation) {
      setError("Password dan konfirmasi password tidak cocok");
      setIsLoading(false);
      return;
    }

    const endpoint = isLogin ? "/api/login" : "/api/register";
    const payload = isLogin
      ? { email, password }
      : {
          name,
          email,
          password,
          password_confirmation: passwordConfirmation, // Kirim ke backend untuk validasi
        };

    try {
      const res = await fetch(
        `${process.env.NEXT_PUBLIC_LARAVEL_API}${endpoint}`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(payload),
        },
      );

      const data = await res.json();

      if (!res.ok) {
        // Handle error validasi Laravel
        if (data.errors) {
          // Ambil pesan error pertama
          const firstField = Object.keys(data.errors)[0];
          const firstError = data.errors[firstField][0];
          throw new Error(firstError);
        }
        throw new Error(data.message || "Terjadi kesalahan");
      }

      onSuccess(data.user, data.access_token);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setIsLoading(false);
    }
  };

  const toggleMode = () => {
    setIsLogin(!isLogin);
    setError("");
    setPassword("");
    setPasswordConfirmation("");
  };

  return (
    <div className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 fade-in">
      <div className="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
        <div className="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h2 className="font-bold text-lg text-gray-800">
            {isLogin ? "Masuk ke Akun Anda" : "Daftar Akun Baru"}
          </h2>
          <button
            onClick={onClose}
            className="p-1 hover:bg-gray-200 rounded-lg transition-colors"
          >
            <X className="w-5 h-5 text-gray-500" />
          </button>
        </div>

        <div className="p-6">
          {customMessage && (
            <div className="mb-5 p-3 bg-amber-50 border border-amber-200 text-amber-700 text-sm rounded-lg text-center font-medium">
              {customMessage}
            </div>
          )}

          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            {!isLogin && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Nama Lengkap
                </label>
                <input
                  type="text"
                  required
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  className="w-full border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 border"
                  placeholder="Nama lengkap"
                  disabled={isLoading}
                />
              </div>
            )}

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Email
              </label>
              <input
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 border"
                placeholder="email@anda.com"
                disabled={isLoading}
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Password
              </label>
              <input
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 border"
                placeholder={
                  isLogin ? "Masukkan password" : "Minimal 8 karakter"
                }
                disabled={isLoading}
              />
              {!isLogin && (
                <p className="text-xs text-gray-500 mt-1">
                  Password minimal 8 karakter
                </p>
              )}
            </div>

            {/* Field Konfirmasi Password - Hanya untuk Register */}
            {!isLogin && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Konfirmasi Password
                </label>
                <input
                  type="password"
                  required
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  className={`w-full rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 border ${
                    passwordConfirmation && password !== passwordConfirmation
                      ? "border-red-500"
                      : "border-gray-300"
                  }`}
                  placeholder="Ulangi password Anda"
                  disabled={isLoading}
                />
                {passwordConfirmation && password !== passwordConfirmation && (
                  <p className="text-xs text-red-500 mt-1">
                    Password tidak cocok
                  </p>
                )}
              </div>
            )}

            <button
              type="submit"
              disabled={isLoading}
              className="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition-colors flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading && <Loader2 className="w-5 h-5 animate-spin" />}
              {isLogin ? "Masuk" : "Daftar"}
            </button>
          </form>

          <div className="mt-6 text-center text-sm text-gray-500">
            {isLogin ? "Belum punya akun?" : "Sudah punya akun?"}
            <button
              onClick={toggleMode}
              className="ml-1 text-emerald-600 font-semibold hover:underline"
              disabled={isLoading}
            >
              {isLogin ? "Daftar Sekarang" : "Masuk di sini"}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
