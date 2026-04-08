import React, { useEffect, useRef, useState } from "react";

import axios from "axios";
import { usePage } from "@inertiajs/react";

import { EmptyLayout } from "@/Layouts/EmptyLayout";

type Conversation = {
    id: string;
    title: string;
    updated_at: string;
};

type ChatMessage = {
    role: "user" | "assistant";
    content: string;
};

type PageProps = {
    conversations: Conversation[];
};

const AiAssistantPage = () => {
    const { conversations: initialConversations } =
        usePage<PageProps>().props;

    const [conversations, setConversations] = useState(initialConversations);
    const [activeConversationId, setActiveConversationId] = useState<
        string | null
    >(null);
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [input, setInput] = useState("");
    const [loading, setLoading] = useState(false);
    const [loadingConversation, setLoadingConversation] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const textareaRef = useRef<HTMLTextAreaElement>(null);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }, [messages]);

    useEffect(() => {
        if (textareaRef.current) {
            textareaRef.current.style.height = "auto";
            textareaRef.current.style.height =
                Math.min(textareaRef.current.scrollHeight, 160) + "px";
        }
    }, [input]);

    const loadConversation = async (conversationId: string) => {
        setLoadingConversation(true);

        try {
            const response = await axios.get(
                `/internal/ai-assistant/conversations/${conversationId}`
            );
            setActiveConversationId(conversationId);
            setMessages(response.data.messages);
        } catch {
            console.error("Failed to load conversation");
        } finally {
            setLoadingConversation(false);
        }
    };

    const startNewConversation = () => {
        setActiveConversationId(null);
        setMessages([]);
        setInput("");
    };

    const sendMessage = async () => {
        const message = input.trim();

        if (!message || loading) {
            return;
        }

        setInput("");
        setMessages((prev) => [...prev, { role: "user", content: message }]);
        setLoading(true);

        try {
            const response = await axios.post("/internal/ai-assistant/chat", {
                message,
                conversation_id: activeConversationId,
            });

            setMessages((prev) => [
                ...prev,
                { role: "assistant", content: response.data.message },
            ]);

            if (!activeConversationId && response.data.conversation_id) {
                setActiveConversationId(response.data.conversation_id);
                setConversations((prev) => [
                    {
                        id: response.data.conversation_id,
                        title: message.substring(0, 60),
                        updated_at: "just now",
                    },
                    ...prev,
                ]);
            }
        } catch {
            setMessages((prev) => [
                ...prev,
                {
                    role: "assistant",
                    content:
                        "Something went wrong. Please try again.",
                },
            ]);
        } finally {
            setLoading(false);
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    return (
        <EmptyLayout title="AI Assistant">
            <div className="flex h-screen bg-gray-100">
                <div className="w-72 bg-white border-r border-gray-200 flex flex-col">
                    <div className="p-4 border-b border-gray-200">
                        <button
                            onClick={startNewConversation}
                            className="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            New conversation
                        </button>
                    </div>

                    <div className="flex-1 overflow-y-auto">
                        {conversations.map((conversation) => (
                            <button
                                key={conversation.id}
                                onClick={() =>
                                    loadConversation(conversation.id)
                                }
                                className={`w-full text-left px-4 py-3 text-sm border-b border-gray-100 hover:bg-gray-50 transition-colors ${
                                    activeConversationId === conversation.id
                                        ? "bg-blue-50 border-l-2 border-l-blue-600"
                                        : ""
                                }`}
                            >
                                <div className="font-medium text-gray-900 truncate">
                                    {conversation.title}
                                </div>
                                <div className="text-xs text-gray-500 mt-1">
                                    {conversation.updated_at}
                                </div>
                            </button>
                        ))}

                        {conversations.length === 0 && (
                            <div className="p-4 text-sm text-gray-500 text-center">
                                No conversations yet
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex-1 flex flex-col">
                    <div className="bg-white border-b border-gray-200 px-6 py-4">
                        <h1 className="text-lg font-semibold text-gray-900">
                            Event Reporting Assistant
                        </h1>
                        <p className="text-sm text-gray-500">
                            Ask questions about events, attendance, and
                            reporting
                        </p>
                    </div>

                    <div className="flex-1 overflow-y-auto px-6 py-4">
                        {loadingConversation ? (
                            <div className="flex items-center justify-center h-full">
                                <div className="text-gray-500">
                                    Loading conversation...
                                </div>
                            </div>
                        ) : messages.length === 0 ? (
                            <div className="flex items-center justify-center h-full">
                                <div className="text-center max-w-md">
                                    <h2 className="text-xl font-semibold text-gray-700 mb-2">
                                        What would you like to know?
                                    </h2>
                                    <p className="text-gray-500 text-sm mb-6">
                                        Ask about event counts, attendee
                                        numbers, breakdowns by type, date
                                        ranges, and more.
                                    </p>
                                    <div className="grid grid-cols-2 gap-2">
                                        {[
                                            "How many fires are currently published?",
                                            "Give me a breakdown of all events this year",
                                            "How many attendees registered for retreats in Q1?",
                                            "How many women's events happened last month?",
                                        ].map((suggestion) => (
                                            <button
                                                key={suggestion}
                                                onClick={() => {
                                                    setInput(suggestion);
                                                    textareaRef.current?.focus();
                                                }}
                                                className="text-left text-xs p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors text-gray-600"
                                            >
                                                {suggestion}
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4 max-w-3xl mx-auto">
                                {messages.map((message, index) => (
                                    <div
                                        key={index}
                                        className={`flex ${
                                            message.role === "user"
                                                ? "justify-end"
                                                : "justify-start"
                                        }`}
                                    >
                                        <div
                                            className={`max-w-[80%] px-4 py-3 rounded-lg text-sm ${
                                                message.role === "user"
                                                    ? "bg-blue-600 text-white"
                                                    : "bg-white border border-gray-200 text-gray-800"
                                            }`}
                                        >
                                            <div className="whitespace-pre-wrap">
                                                {message.content}
                                            </div>
                                        </div>
                                    </div>
                                ))}

                                {loading && (
                                    <div className="flex justify-start">
                                        <div className="bg-white border border-gray-200 px-4 py-3 rounded-lg">
                                            <div className="flex gap-1">
                                                <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.3s]" />
                                                <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.15s]" />
                                                <span className="w-2 h-2 bg-gray-400 rounded-full animate-bounce" />
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div ref={messagesEndRef} />
                            </div>
                        )}
                    </div>

                    <div className="bg-white border-t border-gray-200 px-6 py-4">
                        <div className="max-w-3xl mx-auto flex gap-3">
                            <textarea
                                ref={textareaRef}
                                value={input}
                                onChange={(e) => setInput(e.target.value)}
                                onKeyDown={handleKeyDown}
                                placeholder="Ask about events, attendees, reporting..."
                                rows={1}
                                className="flex-1 resize-none rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-900 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <button
                                onClick={sendMessage}
                                disabled={!input.trim() || loading}
                                className="px-5 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors self-end"
                            >
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </EmptyLayout>
    );
};

export default AiAssistantPage;
