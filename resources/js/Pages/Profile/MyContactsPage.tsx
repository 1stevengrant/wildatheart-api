import React from "react";

import { usePage } from "@inertiajs/react";

import { FilterBar } from "@/Components/FilterBar";
import Pagination from "@/Components/Pagination";
import Table from "@/Components/Table";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { Attendee, PaginatedData } from "@/types";

const MyContactsPage = () => {
    const { attendees } = usePage<{
        attendees: PaginatedData<Attendee>;
    }>().props;

    const {
        data,
        meta: { links },
    } = attendees;

    const pageTitle = `Event Contacts`;

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
                                <>{row.full_name}</>
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
                ]}
                rows={data}
                noDataMessage={"No contacts for this event."}
            />
            <Pagination links={links} />
        </HostDashboardLayout>
    );
};

export default MyContactsPage;
