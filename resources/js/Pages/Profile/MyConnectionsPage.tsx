import React from "react";

import { router, usePage } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import FilterBar from "@/Components/FilterBar";
import Pagination from "@/Components/Pagination";
import Table from "@/Components/Table";
import { HeadingOne } from "@/Components/Type/HeadingOne";
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

    const handleExport = () => {
        const url = new URL(window.location.href);
        const searchParams = new URLSearchParams(url.search);
        const eventId = searchParams.get("event_id");

        router.get(route("export.connections", { event_id: eventId }), {
            replace: true,
            preserveState: true,
        });
    };

    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <div className="flex items-center justify-between border-grey-border border-b pb-6">
                <div className={`flex items-center space-x-5`}>
                    <HeadingOne className={`flex-shrink-0`}>
                        {pageTitle}
                    </HeadingOne>
                    <FilterBar />
                </div>
                <PrimaryButton onClick={handleExport}>
                    Export Connections
                </PrimaryButton>
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
