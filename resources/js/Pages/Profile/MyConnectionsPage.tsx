import React from "react";

import { usePage } from "@inertiajs/react";

import FilterBar from "@/Components/FilterBar";
import Pagination from "@/Components/Pagination";
import Table from "@/Components/Table";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { Attendee, PaginatedData } from "@/types";

const MyConnectionsPage = () => {
    const { attendees } = usePage<{
        attendees: PaginatedData<Attendee>;
    }>().props;

    const {
        data,
        meta: { links },
    } = attendees;

    const pageTitle = `My Connections`;

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <div className="flex items-center justify-between border-grey-border border-b pb-6">
                <div className={`flex items-center space-x-5`}>
                    <HeadingOne className={`flex-shrink-0`}>
                        {pageTitle}
                    </HeadingOne>
                    <FilterBar />
                </div>
                <a
                    className={primaryButtonClasses}
                    href={route("export.connections", {
                        event: route().params.event_id,
                    })}
                >
                    Export Connections
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
            />
            <Pagination links={links} />
        </HostDashboardLayout>
    );
};

export default MyConnectionsPage;
