import React from "react";
import AppLayout from "@/Layouts/AppLayout";
import {usePage} from "@inertiajs/react";
import {Connection} from "@/types/Connection";
import ConnectionCard from "@/Components/ConnectionCard";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";

const MyConnections = () => {
    const [reactNotifications, setReactNotifications] = useRecoilState(reactNotificationAtom)
    const {allConnections, eventConnections} = usePage().props;
    const connections = Object.values(allConnections as Record<string, Connection>);
    const eventConnectionData = (eventConnections as { data: Connection[] })?.data || [];
    const pageTitle = 'My Connections';

    const copyFireEmailAddressesToClipboard = (event: any) => {

        const emails = event.attendees.map((attendee: any) => attendee.email);

        const emailString = emails.join(',');

        const textarea = document.createElement('textarea');

        textarea.value = emailString;

        document.body.appendChild(textarea);

        textarea.select();

        document.execCommand('copy');

        document.body.removeChild(textarea);

        setReactNotifications(prevState => [...prevState, {
            message: 'Emails copied to clipboard!',
            type: 'success',
        }]);

    }

    const copyAllEmailAddressesToClipboard = () => {

        const emails = connections.map((connection: Connection) => connection.email);

        const emailString = emails.join(',');

        const textarea = document.createElement('textarea');

        textarea.value = emailString;

        document.body.appendChild(textarea);

        textarea.select();

        document.execCommand('copy');

        document.body.removeChild(textarea);

        setReactNotifications(prevState => [...prevState, {
            message: 'Emails copied to clipboard!',
            type: 'success',
        }]);
    }

    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                {connections.length > 0 ? (
                    <>
                        <div className="flex items-center space-x-4">
                            <h2 className="text-lg font-medium">All Connections</h2>
                            <PrimaryButton onClick={copyAllEmailAddressesToClipboard}>
                                Copy Email Addresses
                            </PrimaryButton>
                        </div>
                        <ul
                            role="list"
                            className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2"
                        >
                            {connections.map((connection: Connection, index) => (
                                <ConnectionCard
                                    key={index}
                                    connection={connection}/>
                            ))}
                        </ul>
                    </>
                ) : (
                    <p>No connections available.</p>
                )}

                {eventConnectionData.length > 0 && (
                    <div className={`space-y-8 mt-8`}>
                        {eventConnectionData.map((event: any, index) => (
                            <div key={index}>
                                <div className="flex items-center space-x-4">
                                    <h2 className="text-lg font-medium">{event.name} Fire</h2>
                                    {event.attendees?.length > 0 && (
                                        <PrimaryButton onClick={(event) => copyFireEmailAddressesToClipboard(event)}>
                                            Copy Email Addresses
                                        </PrimaryButton>
                                    )}
                                </div>
                                <ul
                                    role="list"
                                    className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2"
                                >
                                    {event.attendees?.length > 0 ? (
                                        event.attendees.map((attendee: any, attendeeIndex: number) => (
                                            <ConnectionCard
                                                key={attendeeIndex}
                                                connection={attendee}/>
                                        ))
                                    ) : (
                                        <p>No connections for this Fire.</p>
                                    )}
                                </ul>
                            </div>
                        ))}
                    </div>
                )}
            </MyProfileWrapper>
        </AppLayout>
    )
}

export default MyConnections;
