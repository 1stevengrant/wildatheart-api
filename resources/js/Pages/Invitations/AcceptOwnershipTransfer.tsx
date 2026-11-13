import { router } from "@inertiajs/react";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { fromCalendarDate } from "@/helpers/calendarDate";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const AcceptOwnershipTransfer = () => {
    const page = useTypedPage();

    const transfer = page.props
        .transfer as App.Data.EventOwnershipTransferWithEventData;

    const handleAccept = () => {
        router.post(route("accept.ownership.transfer.confirm", transfer.id));
    };

    return (
        <HostDashboardLayout pageTitle="Accept Ownership Transfer">
            <HeadingOne>Accept Ownership Transfer</HeadingOne>

            <div className="mt-6 max-w-2xl">
                <div className="bg-n900 border border-n700 rounded-lg p-6">
                    <p className="text-white text-lg mb-4">
                        <strong>{transfer.transferred_by_name}</strong> wants to
                        transfer ownership of their event to you:
                    </p>

                    <div className="bg-black border border-n800 rounded-lg p-6 mb-6">
                        <h2 className="text-2xl font-bold text-white mb-4">
                            {transfer.event.name}{" "}
                            {singular(transfer.event.event_type_name ?? "")}
                        </h2>
                        <p className="text-n40 mb-2">
                            <strong>Location:</strong> {transfer.event.location}
                        </p>
                        {transfer.event.start_date && (
                            <p className="text-n40">
                                <strong>Date:</strong>{" "}
                                {fromCalendarDate(
                                    transfer.event.start_date
                                )?.toLocaleDateString("en-US", {
                                    year: "numeric",
                                    month: "long",
                                    day: "numeric",
                                })}
                            </p>
                        )}
                    </div>

                    <div className="bg-orange-900/20 border border-orange-700 rounded-lg p-4 mb-6">
                        <p className="text-orange-200 text-sm font-semibold mb-2">
                            ⚠️ Important: What This Means
                        </p>
                        <ul className="list-disc list-inside text-orange-100 text-sm space-y-1">
                            <li>
                                You will become the <strong>owner</strong> of
                                this event
                            </li>
                            <li>
                                You will have full control over all event
                                settings
                            </li>
                            <li>
                                The current owner will become a{" "}
                                <strong>host</strong> of this event
                            </li>
                            <li>
                                All attendee records will be transferred to you
                            </li>
                        </ul>
                    </div>

                    <div className="flex space-x-4">
                        <PrimaryButton onClick={handleAccept}>
                            Accept Ownership
                        </PrimaryButton>
                        <a
                            href={route("home")}
                            className="text-n40 hover:text-white inline-block text-center px-6 py-3"
                        >
                            Decline
                        </a>
                    </div>
                </div>
            </div>
        </HostDashboardLayout>
    );
};

export default AcceptOwnershipTransfer;
