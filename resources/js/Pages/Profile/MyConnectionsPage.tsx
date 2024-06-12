import React from "react";

import { usePage } from "@inertiajs/react";

import Pagination from "@/Components/Pagination";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { Attendee, PaginatedData } from "@/types";

const MyConnectionsPage = () => {
    const { attendees } = usePage<{ attendees: PaginatedData<Attendee> }>()
        .props;

    const {
        data,
        meta: { links },
    } = attendees;

    console.log(data);

    return (
        <HostDashboardLayout pageTitle={`Attendees`}>
            <h1>Hello world</h1>
            <Pagination links={links} />
        </HostDashboardLayout>
    );
};

export default MyConnectionsPage;
