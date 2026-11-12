import React, { useState } from "react";

import { router } from "@inertiajs/react";
import { format } from "date-fns";

import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

type CapturedEmail = {
    id: number;
    uuid: string;
    to: string[];
    cc: string[] | null;
    bcc: string[] | null;
    from: string;
    reply_to: string | null;
    subject: string;
    html_body: string | null;
    text_body: string | null;
    mailable_class: string | null;
    headers: Record<string, string> | null;
    attachments: Array<{
        name: string;
        content_type: string;
        body: string;
    }> | null;
    metadata: Record<string, any> | null;
    created_at: string;
};

type Props = {
    email: CapturedEmail;
};

const Show = () => {
    const { email } = useTypedPage<Props>().props;
    const [viewMode, setViewMode] = useState<"html" | "text">(
        email.html_body ? "html" : "text"
    );
    const [showHeaders, setShowHeaders] = useState(false);

    const handleDelete = () => {
        if (confirm("Are you sure you want to delete this email?")) {
            router.delete(`/internal/emails/${email.uuid}`, {
                onSuccess: () => router.visit("/internal/emails"),
            });
        }
    };

    const downloadAttachment = (
        attachment: { name: string; content_type: string; body: string },
        index: number
    ) => {
        const byteCharacters = atob(attachment.body);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: attachment.content_type });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = attachment.name || `attachment-${index}`;
        link.click();
        window.URL.revokeObjectURL(url);
    };

    return (
        <EmptyLayout title={`Email: ${email.subject}`}>
            <div className="p-8">
                <div className="mb-4 flex justify-between items-center">
                    <a
                        href="/internal/emails"
                        className="text-blue-600 hover:text-blue-900"
                    >
                        ← Back to Emails
                    </a>
                    <button
                        onClick={handleDelete}
                        className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                    >
                        Delete Email
                    </button>
                </div>

                <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    {/* Email Header */}
                    <div className="p-6 border-b border-gray-200">
                        <h1 className="text-2xl font-bold text-gray-900 mb-4">
                            {email.subject}
                        </h1>

                        <div className="space-y-2 text-sm">
                            <div className="flex">
                                <span className="font-semibold w-24 text-gray-700">
                                    From:
                                </span>
                                <span className="text-gray-900">
                                    {email.from}
                                </span>
                            </div>

                            <div className="flex">
                                <span className="font-semibold w-24 text-gray-700">
                                    To:
                                </span>
                                <span className="text-gray-900">
                                    {email.to.join(", ")}
                                </span>
                            </div>

                            {email.cc && email.cc.length > 0 && (
                                <div className="flex">
                                    <span className="font-semibold w-24 text-gray-700">
                                        CC:
                                    </span>
                                    <span className="text-gray-900">
                                        {email.cc.join(", ")}
                                    </span>
                                </div>
                            )}

                            {email.bcc && email.bcc.length > 0 && (
                                <div className="flex">
                                    <span className="font-semibold w-24 text-gray-700">
                                        BCC:
                                    </span>
                                    <span className="text-gray-900">
                                        {email.bcc.join(", ")}
                                    </span>
                                </div>
                            )}

                            {email.reply_to && (
                                <div className="flex">
                                    <span className="font-semibold w-24 text-gray-700">
                                        Reply-To:
                                    </span>
                                    <span className="text-gray-900">
                                        {email.reply_to}
                                    </span>
                                </div>
                            )}

                            <div className="flex">
                                <span className="font-semibold w-24 text-gray-700">
                                    Sent:
                                </span>
                                <span className="text-gray-900">
                                    {format(
                                        new Date(email.created_at),
                                        "MMMM d, yyyy 'at' h:mm:ss a"
                                    )}
                                </span>
                            </div>

                            {email.mailable_class && (
                                <div className="flex">
                                    <span className="font-semibold w-24 text-gray-700">
                                        Type:
                                    </span>
                                    <span className="text-gray-600 font-mono text-xs">
                                        {email.mailable_class}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Attachments */}
                        {email.attachments && email.attachments.length > 0 && (
                            <div className="mt-4 pt-4 border-t border-gray-200">
                                <span className="font-semibold text-gray-700 text-sm">
                                    Attachments ({email.attachments.length}):
                                </span>
                                <div className="mt-2 space-y-2">
                                    {email.attachments.map(
                                        (attachment, index) => (
                                            <button
                                                key={index}
                                                onClick={() =>
                                                    downloadAttachment(
                                                        attachment,
                                                        index
                                                    )
                                                }
                                                className="flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700"
                                            >
                                                <svg
                                                    className="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                                <span>
                                                    {attachment.name ||
                                                        `Attachment ${index + 1}`}
                                                </span>
                                                <span className="text-xs text-gray-500">
                                                    ({attachment.content_type})
                                                </span>
                                            </button>
                                        )
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* View Toggle */}
                    <div className="px-6 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div className="flex gap-2">
                            {email.html_body && (
                                <button
                                    onClick={() => setViewMode("html")}
                                    className={`px-4 py-2 rounded text-sm font-medium ${
                                        viewMode === "html"
                                            ? "bg-blue-600 text-white"
                                            : "bg-white text-gray-700 hover:bg-gray-100"
                                    }`}
                                >
                                    HTML
                                </button>
                            )}
                            {email.text_body && (
                                <button
                                    onClick={() => setViewMode("text")}
                                    className={`px-4 py-2 rounded text-sm font-medium ${
                                        viewMode === "text"
                                            ? "bg-blue-600 text-white"
                                            : "bg-white text-gray-700 hover:bg-gray-100"
                                    }`}
                                >
                                    Plain Text
                                </button>
                            )}
                        </div>

                        <button
                            onClick={() => setShowHeaders(!showHeaders)}
                            className="text-sm text-blue-600 hover:text-blue-900"
                        >
                            {showHeaders ? "Hide" : "Show"} Headers
                        </button>
                    </div>

                    {/* Email Body */}
                    <div className="p-6">
                        {viewMode === "html" && email.html_body ? (
                            <iframe
                                srcDoc={email.html_body}
                                className="w-full border border-gray-300 rounded"
                                style={{ minHeight: "600px" }}
                                sandbox="allow-same-origin"
                                title="Email HTML Preview"
                            />
                        ) : (
                            <pre className="whitespace-pre-wrap font-sans text-sm text-gray-900 bg-gray-50 p-4 rounded border border-gray-200">
                                {email.text_body || "No text content available"}
                            </pre>
                        )}
                    </div>

                    {/* Headers */}
                    {showHeaders && email.headers && (
                        <div className="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <h3 className="font-semibold text-gray-900 mb-2">
                                Email Headers
                            </h3>
                            <div className="bg-white p-4 rounded border border-gray-200">
                                <pre className="text-xs font-mono text-gray-700 whitespace-pre-wrap">
                                    {Object.entries(email.headers)
                                        .map(
                                            ([key, value]) => `${key}: ${value}`
                                        )
                                        .join("\n")}
                                </pre>
                            </div>
                        </div>
                    )}

                    {/* Metadata */}
                    {email.metadata &&
                        Object.keys(email.metadata).length > 0 && (
                            <div className="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                <h3 className="font-semibold text-gray-900 mb-2">
                                    Metadata
                                </h3>
                                <div className="bg-white p-4 rounded border border-gray-200">
                                    <pre className="text-xs font-mono text-gray-700">
                                        {JSON.stringify(
                                            email.metadata,
                                            null,
                                            2
                                        )}
                                    </pre>
                                </div>
                            </div>
                        )}
                </div>
            </div>
        </EmptyLayout>
    );
};

export default Show;
