import React, { useState } from "react";

import { router } from "@inertiajs/react";
import dayjs from "dayjs";

import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

type CapturedEmail = {
    id: number;
    uuid: string;
    to: string[];
    subject: string;
    mailable_class: string | null;
    created_at: string;
};

type Props = {
    emails: {
        data: CapturedEmail[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    mailableClasses: string[];
    filters: {
        search?: string;
        mailable?: string;
        from?: string;
        to?: string;
    };
};

const Index = () => {
    const { emails, mailableClasses, filters } = useTypedPage<Props>().props;
    const [search, setSearch] = useState(filters.search || "");
    const [mailable, setMailable] = useState(filters.mailable || "");
    const [deleting, setDeleting] = useState<string | null>(null);

    const handleSearch = () => {
        router.get(
            "/internal/emails",
            { search, mailable },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleClearFilters = () => {
        setSearch("");
        setMailable("");
        router.get("/internal/emails");
    };

    const handleDelete = (uuid: string) => {
        if (confirm("Are you sure you want to delete this email?")) {
            setDeleting(uuid);
            router.delete(`/internal/emails/${uuid}`, {
                onFinish: () => setDeleting(null),
            });
        }
    };

    const handleClearAll = () => {
        if (
            confirm(
                `Are you sure you want to delete all ${emails.total} captured emails? This cannot be undone.`
            )
        ) {
            router.delete("/internal/emails");
        }
    };

    return (
        <EmptyLayout title="Captured Emails">
            <div className="p-8">
                <div className="bg-white shadow-sm sm:rounded-lg">
                    <div className="p-6 border-b border-gray-200">
                        <div className="flex justify-between items-center mb-4">
                            <h1 className="text-2xl font-bold text-gray-900">
                                Captured Emails
                            </h1>
                            {emails.total > 0 && (
                                <button
                                    onClick={handleClearAll}
                                    className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                                >
                                    Clear All ({emails.total})
                                </button>
                            )}
                        </div>

                        <div className="flex gap-4">
                            <input
                                type="text"
                                placeholder="Search subject or recipient..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) =>
                                    e.key === "Enter" && handleSearch()
                                }
                                className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />

                            <select
                                value={mailable}
                                onChange={(e) => setMailable(e.target.value)}
                                className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">All Types</option>
                                {mailableClasses.map((className) => (
                                    <option key={className} value={className}>
                                        {className.split("\\").pop()}
                                    </option>
                                ))}
                            </select>

                            <button
                                onClick={handleSearch}
                                className="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Filter
                            </button>

                            {(search || mailable) && (
                                <button
                                    onClick={handleClearFilters}
                                    className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                                >
                                    Clear
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        {emails.data.length === 0 ? (
                            <div className="p-12 text-center text-gray-500">
                                <p className="text-lg">
                                    No emails captured yet
                                </p>
                                <p className="text-sm mt-2">
                                    Emails will appear here when using the
                                    database mail driver
                                </p>
                            </div>
                        ) : (
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Recipient(s)
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subject
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sent At
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {emails.data.map((email) => (
                                        <tr
                                            key={email.uuid}
                                            className="hover:bg-gray-50"
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {email.to.join(", ")}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="text-sm text-gray-900">
                                                    {email.subject}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-xs text-gray-500">
                                                    {email.mailable_class
                                                        ?.split("\\")
                                                        .pop() || "N/A"}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {dayjs(email.created_at).format(
                                                    "MMM D, YYYY h:mm A"
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a
                                                    href={`/internal/emails/${email.uuid}`}
                                                    className="text-blue-600 hover:text-blue-900 mr-4"
                                                >
                                                    View
                                                </a>
                                                <button
                                                    onClick={() =>
                                                        handleDelete(email.uuid)
                                                    }
                                                    disabled={
                                                        deleting === email.uuid
                                                    }
                                                    className="text-red-600 hover:text-red-900 disabled:opacity-50"
                                                >
                                                    {deleting === email.uuid
                                                        ? "Deleting..."
                                                        : "Delete"}
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {emails.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                            <div className="text-sm text-gray-500">
                                Showing{" "}
                                {(emails.current_page - 1) * emails.per_page +
                                    1}{" "}
                                to{" "}
                                {Math.min(
                                    emails.current_page * emails.per_page,
                                    emails.total
                                )}{" "}
                                of {emails.total} emails
                            </div>
                            <div className="flex gap-2">
                                {emails.links.map((link, index) => (
                                    <button
                                        key={index}
                                        onClick={() =>
                                            link.url && router.get(link.url)
                                        }
                                        disabled={!link.url}
                                        className={`px-3 py-1 rounded ${
                                            link.active
                                                ? "bg-blue-600 text-white"
                                                : link.url
                                                  ? "bg-gray-200 hover:bg-gray-300"
                                                  : "bg-gray-100 text-gray-400 cursor-not-allowed"
                                        }`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </EmptyLayout>
    );
};

export default Index;
