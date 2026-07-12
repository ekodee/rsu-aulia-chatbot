import { useEffect, useRef } from "react";
import { User, Bot, Loader2 } from "lucide-react";
import ReactMarkdown from "react-markdown";
import WelcomeScreen from "./WelcomeScreen";

type Message = { id: string; role: "user" | "ai"; content: string };

interface MessageListProps {
  messages: Message[];
  isLoading: boolean;
  user: any;
  guestChatCount: number;
  onSuggestionClick: (suggestion: string) => void;
  onOpenAuth: (mode: "login" | "register") => void; // DITAMBAHKAN
}

export default function MessageList({
  messages,
  isLoading,
  user,
  guestChatCount,
  onSuggestionClick,
  onOpenAuth, // DITERIMA
}: MessageListProps) {
  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Auto-scroll
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages, isLoading]);

  return (
    <main className="flex-1 overflow-y-auto p-4 pt-20 pb-32 relative">
      {!user && messages.length > 0 && guestChatCount < 3 && (
        <div className="sticky top-0 z-20 max-w-6xl mx-auto mb-6">
          <div className="bg-amber-50/95 backdrop-blur-sm text-amber-700 p-3 rounded-lg text-center text-sm border border-amber-200 shadow-sm">
            Anda menggunakan mode Guest. Sisa kuota pertanyaan Anda:{" "}
            <strong>{3 - guestChatCount} kali</strong>.
          </div>
        </div>
      )}

      <div className="max-w-3xl mx-auto space-y-6">
        {messages.length === 0 && (
          <WelcomeScreen
            onSuggestionClick={onSuggestionClick}
            isGuest={!user}
            onOpenAuth={onOpenAuth} // DITERUSKAN KE WELCOME SCREEN
          />
        )}

        {messages.map((msg) => (
          <div
            key={msg.id}
            className={`flex gap-4 ${msg.role === "user" ? "justify-end" : "justify-start"} fade-in`}
          >
            {msg.role === "ai" && (
              <div className="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 border border-emerald-200 mt-1">
                <Bot className="w-5 h-5 text-emerald-600" />
              </div>
            )}
            <div
              className={`max-w-[85%] md:max-w-[75%] p-4 rounded-2xl text-sm md:text-base leading-relaxed ${msg.role === "user" ? "bg-emerald-600 text-white rounded-tr-none shadow-md" : "bg-gray-50 border border-gray-100 shadow-sm rounded-tl-none text-gray-800"}`}
            >
              {msg.role === "ai" ? (
                <div className="prose prose-sm md:prose-base prose-emerald max-w-none">
                  <ReactMarkdown>{msg.content}</ReactMarkdown>
                </div>
              ) : (
                <p className="whitespace-pre-wrap">{msg.content}</p>
              )}
            </div>
            {msg.role === "user" && (
              <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center shrink-0 border border-gray-300 mt-1">
                <User className="w-5 h-5 text-gray-500" />
              </div>
            )}
          </div>
        ))}

        {isLoading && (
          <div className="flex gap-4 justify-start fade-in">
            <div className="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 border border-emerald-200 mt-1">
              <Bot className="w-5 h-5 text-emerald-600" />
            </div>
            <div className="bg-gray-50 border border-gray-100 shadow-sm p-4 rounded-2xl rounded-tl-none flex items-center gap-3">
              <Loader2 className="w-5 h-5 text-emerald-500 animate-spin" />
              <span className="text-sm text-gray-500">
                AI sedang mengetik...
              </span>
            </div>
          </div>
        )}

        <div ref={messagesEndRef} />
      </div>
    </main>
  );
}
