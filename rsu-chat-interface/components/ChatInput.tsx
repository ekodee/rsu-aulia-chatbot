import { Send } from "lucide-react";

interface ChatInputProps {
  input: string;
  setInput: (value: string) => void;
  onSubmit: (e?: React.FormEvent) => void;
  isLoading: boolean;
}

export default function ChatInput({ input, setInput, onSubmit, isLoading }: ChatInputProps) {
  return (
    <div className="absolute bottom-0 w-full bg-gradient-to-t from-white via-white to-transparent pt-6 pb-6 px-4">
      <form onSubmit={onSubmit} className="max-w-3xl mx-auto flex gap-3 items-end relative shadow-lg rounded-2xl bg-white border border-gray-200 p-2">
        <textarea
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === "Enter" && !e.shiftKey) {
              e.preventDefault();
              onSubmit();
            }
          }}
          placeholder="Ketik pertanyaan Anda di sini..."
          className="flex-1 resize-none bg-transparent border-none focus:ring-0 px-3 py-3 max-h-[150px] min-h-[44px] outline-none text-gray-700"
          rows={1}
        />
        <button
          type="submit"
          disabled={!input.trim() || isLoading}
          className="bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-200 disabled:text-gray-400 text-white p-3 rounded-xl transition-all shrink-0 h-[48px] w-[48px] flex items-center justify-center shadow-sm"
        >
          <Send className="w-5 h-5" />
        </button>
      </form>
      <div className="text-center mt-3">
        <p className="text-[11px] text-gray-400">
          AI dapat melakukan kesalahan. Harap verifikasi informasi medis atau hubungi Customer Service.
        </p>
      </div>
    </div>
  );
}