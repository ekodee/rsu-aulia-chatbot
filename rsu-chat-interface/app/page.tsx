"use client";

import { useState, useRef, useEffect, useCallback } from "react";
import { User, Bot, Loader2, Hospital, Menu, LogOut } from "lucide-react";
import ReactMarkdown from "react-markdown";

import Sidebar from "@/components/Sidebar";
import WelcomeScreen from "@/components/WelcomeScreen";
import ChatInput from "@/components/ChatInput";
import AuthModal from "@/components/AuthModal";
import ChatHeader from "@/components/ChatHeader";
import MessageList from "@/components/MessageList";

type Message = { id: string; role: "user" | "ai"; content: string };

export default function ChatUI() {
  const [messages, setMessages] = useState<Message[]>([]);
  const [input, setInput] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  // State untuk Autentikasi & Kuota
  const [user, setUser] = useState<any>(null);
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const [authModalMessage, setAuthModalMessage] = useState("");
  const [authMode, setAuthMode] = useState<"login" | "register">("login");
  const [guestChatCount, setGuestChatCount] = useState(0);

  // --- STATE BARU UNTUK RIWAYAT CHAT ---
  const [chatSessions, setChatSessions] = useState<any[]>([]);
  const [currentSessionId, setCurrentSessionId] = useState<number | null>(null);

  const messagesEndRef = useRef<HTMLDivElement>(null);

  // Fungsi untuk mengambil daftar riwayat dari Laravel
  const fetchSessions = useCallback(async (token: string) => {
    try {
      const res = await fetch("http://localhost:8001/api/chat-sessions", {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (res.ok) {
        const data = await res.json();
        setChatSessions(data);
      }
    } catch (error) {
      console.error("Gagal mengambil riwayat:", error);
    }
  }, []);

  useEffect(() => {
    scrollToBottom();
    const savedCount = localStorage.getItem("guestChatCount");
    if (savedCount) setGuestChatCount(parseInt(savedCount));

    const token = localStorage.getItem("auth_token");
    const savedUser = localStorage.getItem("user_data");
    if (token && savedUser) {
      setUser(JSON.parse(savedUser));
      fetchSessions(token);
    }
  }, [messages, fetchSessions]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  // --- FUNGSI UNTUK MEMBUKA MODAL LOGIN/REGISTER ---
  const handleOpenAuth = (mode: "login" | "register") => {
    setAuthMode(mode);
    setAuthModalMessage("");
    setIsAuthModalOpen(true);
  };

  const handleAuthSuccess = (userData: any, token: string) => {
    localStorage.setItem("auth_token", token);
    localStorage.setItem("user_data", JSON.stringify(userData));
    setUser(userData);
    setIsAuthModalOpen(false);
    fetchSessions(token);
  };

  const handleLogout = () => {
    localStorage.removeItem("auth_token");
    localStorage.removeItem("user_data");
    setUser(null);
    setMessages([]);
    setChatSessions([]);
    setCurrentSessionId(null);
  };

  const handleNewChat = () => {
    setMessages([]);
    setCurrentSessionId(null);
  };

  const loadSession = async (id: number) => {
    const token = localStorage.getItem("auth_token");
    if (!token) return;

    try {
      const res = await fetch(`http://localhost:8001/api/chat-sessions/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });

      if (res.ok) {
        const data = await res.json();
        const loadedMessages = data.messages.map((msg: any) => ({
          id: msg.id.toString(),
          role: msg.role,
          content: msg.content,
        }));
        setMessages(loadedMessages);
        setCurrentSessionId(id);
      }
    } catch (error) {
      console.error("Gagal memuat percakapan:", error);
    }
  };

  const sendMessage = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!input.trim() || isLoading) return;

    if (!user && guestChatCount >= 3) {
      setAuthModalMessage(
        "Batas 3x pertanyaan gratis untuk Mode Guest telah habis. Silakan Login atau Daftar secara gratis untuk melanjutkan percakapan tanpa batas!",
      );
      setIsAuthModalOpen(true);
      return;
    }

    const formattedHistory = messages.map((msg) => ({
      role: msg.role,
      content: msg.content,
    }));

    const userMessage: Message = {
      id: Date.now().toString(),
      role: "user",
      content: input,
    };

    setMessages((prev) => [...prev, userMessage]);
    setInput("");
    setIsLoading(true);

    try {
      const response = await fetch("http://127.0.0.1:8000/api/chat", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          question: userMessage.content,
          chat_history: formattedHistory,
        }),
      });

      if (!response.body) throw new Error("No response body");

      if (!user) {
        setGuestChatCount((prevCount) => {
          const updatedCount = prevCount + 1;
          localStorage.setItem("guestChatCount", updatedCount.toString());
          return updatedCount;
        });
      }

      const aiMessageId = (Date.now() + 1).toString();

      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let done = false;
      let fullAiResponse = "";
      let isFirstChunk = true;

      while (!done) {
        const { value, done: doneReading } = await reader.read();
        done = doneReading;

        if (value) {
          const chunkValue = decoder.decode(value, { stream: true });
          const parts = chunkValue.split("data: ");

          for (const part of parts) {
            if (!part.trim() || part.trim() === "[DONE]") continue;

            let textContent = part.endsWith("\n\n") ? part.slice(0, -2) : part;
            fullAiResponse += textContent;

            if (isFirstChunk) {
              setIsLoading(false);
              isFirstChunk = false;
              setMessages((prev) => [
                ...prev,
                { id: aiMessageId, role: "ai", content: textContent },
              ]);
            } else {
              setMessages((prev) =>
                prev.map((msg) =>
                  msg.id === aiMessageId
                    ? { ...msg, content: msg.content + textContent }
                    : msg,
                ),
              );
            }
          }
        }
      }

      if (user) {
        const token = localStorage.getItem("auth_token");
        const saveRes = await fetch("http://localhost:8001/api/chat-sessions", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            session_id: currentSessionId,
            user_message: userMessage.content,
            ai_message: fullAiResponse,
          }),
        });

        if (saveRes.ok) {
          const saveData = await saveRes.json();
          // Jika ini chat baru, Laravel akan mengembalikan ID sesi baru
          if (!currentSessionId) {
            setCurrentSessionId(saveData.session_id);
            fetchSessions(token as string); // Refresh sidebar agar judul baru muncul
          }
        }
      }
    } catch (error) {
      setIsLoading(false);
      setMessages((prev) => [
        ...prev,
        {
          id: Date.now().toString(),
          role: "ai",
          content: "Maaf, koneksi ke server terputus.",
        },
      ]);
    }
  };

  const deleteSession = async (id: number) => {
    const token = localStorage.getItem("auth_token");
    if (!token) return;

    if (
      !window.confirm(
        "Apakah Anda yakin ingin menghapus riwayat percakapan ini?",
      )
    )
      return;

    try {
      const res = await fetch(`http://localhost:8001/api/chat-sessions/${id}`, {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });

      if (res.ok) {
        if (currentSessionId === id) {
          setMessages([]);
          setCurrentSessionId(null);
        }
        fetchSessions(token);
      }
    } catch (error) {
      console.error("Gagal menghapus percakapan:", error);
    }
  };

  return (
    <div className="flex h-screen bg-white font-sans text-gray-900 overflow-hidden">
      <Sidebar
        isOpen={isSidebarOpen}
        onNewChat={handleNewChat}
        sessions={chatSessions}
        onSelectSession={loadSession}
        onDeleteSession={deleteSession}
        currentSessionId={currentSessionId}
        isLoggedIn={!!user}
      />

      <div className="flex-1 flex flex-col h-full relative">
        <ChatHeader
          user={user}
          isSidebarOpen={isSidebarOpen}
          setIsSidebarOpen={setIsSidebarOpen}
          onLogout={handleLogout}
          onOpenAuth={handleOpenAuth} // KABEL TERSAMBUNG DI SINI
        />

        <MessageList
          messages={messages}
          isLoading={isLoading}
          user={user}
          guestChatCount={guestChatCount}
          onSuggestionClick={(suggestion) => setInput(suggestion)}
          onOpenAuth={handleOpenAuth} // KABEL TERSAMBUNG DI SINI JUGA
        />

        <ChatInput
          input={input}
          setInput={setInput}
          onSubmit={sendMessage}
          isLoading={isLoading}
        />
      </div>

      <AuthModal
        isOpen={isAuthModalOpen}
        onClose={() => setIsAuthModalOpen(false)}
        onSuccess={handleAuthSuccess}
        customMessage={authModalMessage}
        defaultIsLogin={authMode === "login"}
      />

      <style jsx global>{`
        .fade-in {
          animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(10px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
      `}</style>
    </div>
  );
}
