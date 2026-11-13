import React from "react";

import { TrashIcon } from "@heroicons/react/24/outline";
import { router } from "@inertiajs/react";

import { FilterBar } from "@/Components/FilterBar";
import Pagination from "@/Components/Pagination";
import Table from "@/Components/Table";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { PaginatedData } from "@/types";

const MyContactsPage = () => {
    const { attendees } = useTypedPage<{
        attendees: PaginatedData<App.Data.AttendeeData>;
    }>().props;

    const {
        data,
        meta: { links },
    } = attendees;

    const pageTitle = `Event Contacts`;

    const deleteContact = (attendeeId: string | number) => {
        if (confirm("Are you sure you want to delete this contact?")) {
            router.delete(
                route("my.contacts.destroy", {
                    attendee: attendeeId,
                }),
                {
                    preserveScroll: true,
                    preserveState: false,
                }
            );
        }
    };

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <div className="flex flex-col md:flex-row md:items-center md:justify-between border-n700 border-b pb-6">
                <div className={`flex items-center space-x-5`}>
                    <HeadingOne className={`flex-shrink-0`}>
                        {pageTitle}
                    </HeadingOne>
                    <FilterBar />
                </div>
                <a
                    className={`${primaryButtonClasses} mt-4 md:mt-0`}
                    href={route("export.contacts", {
                        event: route().params.event_id,
                    })}
                >
                    Export Contacts
                </a>
            </div>

            <Table
                columns={[
                    {
                        label: "Name",
                        name: "name",
                        renderCell: (row) => (
                            <>
                                <>{row.fullname}</>
                            </>
                        ),
                    },
                    {
                        label: "Email",
                        name: "email",
                        renderCell: (row) => (
                            <>
                                <button>{row.email}</button>
                            </>
                        ),
                    },
                    {
                        label: "Phone",
                        name: "phone",
                        renderCell: (row) => (
                            <>
                                <>{row.phone}</>
                            </>
                        ),
                    },
                    {
                        label: "Event",
                        name: "event_name",
                        renderCell: (row) => <>{row.event_name}</>,
                    },
                    {
                        label: "Event Type",
                        name: "event_type_name",
                        renderCell: (row) => (
                            <>
                                <>{row.event_type_name}</>
                            </>
                        ),
                    },
                    {
                        label: "First contacted",
                        name: "created_at",
                        renderCell: (row) => (
                            <>
                                <>{row.created_at}</>
                            </>
                        ),
                    },
                    {
                        label: "Actions",
                        name: "actions",
                        renderCell: (row) => (
                            <>
                                <button
                                    onClick={() => deleteContact(row.id)}
                                    className="bg-red-600 hover:bg-red-500 transition-all ease-in-out duration-100 text-white rounded-md p-2"
                                >
                                    <TrashIcon className="size-4" />
                                </button>
                            </>
                        ),
                    },
                ]}
                rows={data}
                noDataMessage={"No contacts for this event."}
            />
            <Pagination links={links} />
        </HostDashboardLayout>
    );
};

export default MyContactsPage;
