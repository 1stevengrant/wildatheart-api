import { useState } from "react";

import { router, usePage } from "@inertiajs/react";
import { singular } from "pluralize";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { CompleteProfileModal } from "@/Components/Modals/CompleteProfileModal";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { fromCalendarDate } from "@/helpers/calendarDate";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const AcceptHostInvitation = () => {
    const page = useTypedPage();
    const { auth } = usePage().props;

    const invitation = page.props
        .invitation as App.Data.EventManagerInvitationWithEventData;
    const [showProfileModal, setShowProfileModal] = useState(false);

    const handleAccept = () => {
        if (!auth.user?.has_complete_profile) {
            setShowProfileModal(true);
            return;
        }

        router.post(route("accept.host.invitation.confirm", invitation.id));
    };

    const handleProfileComplete = () => {
        router.post(route("accept.host.invitation.confirm", invitation.id));
    };

    return (
        <HostDashboardLayout pageTitle="Accept Host Invitation">
            <HeadingOne>Accept Host Invitation</HeadingOne>

            <div className="mt-6 max-w-2xl">
                <div className="bg-n900 border border-n700 rounded-lg p-6">
                    <p className="text-white text-lg mb-4">
                        <strong>{invitation.invited_by_name}</strong> has
                        invited you to be a host for:
                    </p>

                    <div className="bg-black border border-n800 rounded-lg p-6 mb-6">
                        <h2 className="text-2xl font-bold text-white mb-4">
                            {invitation.event.name}{" "}
                            {singular(invitation.event.event_type_name ?? "")}
                        </h2>
                        <p className="text-n40 mb-2">
                            <strong>Location:</strong>{" "}
                            {invitation.event.location}
                        </p>
                        {invitation.event.start_date && (
                            <p className="text-n40">
                                <strong>Date:</strong>{" "}
                                {fromCalendarDate(
                                    invitation.event.start_date
                                )?.toLocaleDateString("en-US", {
                                    year: "numeric",
                                    month: "long",
                                    day: "numeric",
                                })}
                            </p>
                        )}
                    </div>

                    <div className="bg-n800 border border-n700 rounded-lg p-4 mb-6">
                        <p className="text-white text-sm">
                            <strong>As a host, you will be able to:</strong>
                        </p>
                        <ul className="list-disc list-inside text-n40 text-sm mt-2 space-y-1">
                            <li>Help manage this event</li>
                            <li>Edit event details</li>
                            <li>View and contact attendees</li>
                            <li>Invite additional hosts</li>
                        </ul>
                    </div>

                    <div className="flex space-x-4">
                        <PrimaryButton onClick={handleAccept}>
                            Accept Invitation
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

            <CompleteProfileModal
                open={showProfileModal}
                setOpen={setShowProfileModal}
                onSuccess={handleProfileComplete}
            />
        </HostDashboardLayout>
    );
};

export default AcceptHostInvitation;
