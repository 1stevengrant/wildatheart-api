import React, { useState } from "react";

import { XMarkIcon, StarIcon } from "@heroicons/react/24/outline";
import { router } from "@inertiajs/react";

import { PrimaryButton } from "@/Components/Buttons/PrimaryButton";
import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import { Instruction } from "@/Components/Forms/Instruction";
import TextInput from "@/Components/Forms/TextInput";
import useRoute from "@/Hooks/useRoute";

interface EventHostsProps {
    eventId: string;
    managers: App.Data.EventManagerData[];
    pendingTransfer?: App.Data.EventOwnershipTransferData | null;
    isOwner?: boolean;
}

export const EventHosts = ({
    eventId,
    managers: initialManagers,
    pendingTransfer,
    isOwner = false,
}: EventHostsProps) => {
    const route = useRoute();
    const [email, setEmail] = useState("");
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<{ email?: string }>({});

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        router.post(
            route("events.managers.store", eventId),
            { email },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEmail("");
                    setProcessing(false);
                },
                onError: (errors) => {
                    setErrors(errors);
                    setProcessing(false);
                },
            }
        );
    };

    const handleRemove = (managerId: string, managerName: string) => {
        if (
            !confirm(
                `Are you sure you want to remove ${managerName} as a host?`
            )
        ) {
            return;
        }

        router.delete(
            route("events.managers.destroy", {
                event: eventId,
                manager: managerId,
            }),
            {
                preserveScroll: true,
            }
        );
    };

    const handlePromoteToOwner = (
        managerEmail: string,
        managerName: string
    ) => {
        if (
            !confirm(
                `Are you sure you want to transfer ownership to ${managerName}?\n\nYou will become a host and ${managerName} will become the owner.`
            )
        ) {
            return;
        }

        router.post(
            route("events.ownership.store", eventId),
            { email: managerEmail },
            {
                preserveScroll: true,
            }
        );
    };

    const handleCancelTransfer = () => {
        if (
            !confirm("Are you sure you want to cancel this ownership transfer?")
        ) {
            return;
        }

        router.delete(route("events.ownership.destroy", eventId), {
            preserveScroll: true,
        });
    };

    return (
        <div id="hosts" className="p-6 border border-n700 rounded-lg">
            <h3 className="text-xl font-semibold text-white mb-4">Hosts</h3>

            <Instruction>
                Hosts can help you manage this event. They will have access to
                edit event details and view attendees. You can invite people by
                email, even if they don't have an account yet.
            </Instruction>

            {initialManagers.length > 0 && (
                <div className="mt-4 space-y-2">
                    {initialManagers.map((manager) => {
                        const hasPendingTransfer =
                            pendingTransfer &&
                            pendingTransfer.email === manager.email;

                        return (
                            <div
                                key={manager.id}
                                className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 bg-n900 rounded"
                            >
                                <div className="flex items-center space-x-3 min-w-0 flex-1">
                                    <img
                                        src={manager.image}
                                        alt={manager.fullname}
                                        className="w-10 h-10 rounded-full flex-shrink-0"
                                    />
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center space-x-2 flex-wrap">
                                            <p className="text-white font-medium truncate">
                                                {manager.is_pending
                                                    ? manager.email
                                                    : manager.fullname}
                                            </p>
                                            {manager.is_owner && (
                                                <span className="text-xxs px-2 py-0.5 rounded-full bg-green-900/30 text-green-400 border border-green-700 whitespace-nowrap">
                                                    Owner
                                                </span>
                                            )}
                                            {manager.is_pending && (
                                                <span className="text-xxs px-2 py-0.5 rounded-full bg-yellow-900/30 text-yellow-400 border border-yellow-700 whitespace-nowrap">
                                                    Pending Host
                                                </span>
                                            )}
                                            {hasPendingTransfer && (
                                                <span className="text-xxs px-2 py-0.5 rounded-full bg-blue-900/30 text-blue-400 border border-blue-700 whitespace-nowrap">
                                                    Pending Owner Transfer
                                                </span>
                                            )}
                                        </div>
                                        {!manager.is_pending && (
                                            <p className="text-sm text-n40 truncate">
                                                {manager.email}
                                            </p>
                                        )}
                                        {manager.is_pending && (
                                            <p className="text-xs text-n40">
                                                Invitation sent - will become
                                                host when they sign up
                                            </p>
                                        )}
                                        {hasPendingTransfer && (
                                            <p className="text-xs text-blue-400">
                                                Invited to become owner
                                            </p>
                                        )}
                                    </div>
                                </div>
                                <div className="flex items-center space-x-2 flex-shrink-0 sm:ml-auto">
                                    {isOwner &&
                                        !manager.is_owner &&
                                        !manager.is_pending &&
                                        !hasPendingTransfer && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    handlePromoteToOwner(
                                                        manager.email,
                                                        manager.fullname
                                                    )
                                                }
                                                className="flex items-center space-x-1.5 text-yellow-400 hover:text-yellow-300 transition-colors text-xs px-2 py-1 rounded border border-yellow-700 hover:border-yellow-600"
                                                title="Transfer ownership to this host"
                                            >
                                                <StarIcon className="w-4 h-4" />
                                                <span>Promote to Owner</span>
                                            </button>
                                        )}
                                    {hasPendingTransfer && isOwner && (
                                        <button
                                            type="button"
                                            onClick={handleCancelTransfer}
                                            className="text-orange-400 hover:text-orange-300 transition-colors text-xs px-2 py-1 rounded border border-orange-700"
                                            title="Cancel transfer"
                                        >
                                            Cancel Transfer
                                        </button>
                                    )}
                                    {!manager.is_owner && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                handleRemove(
                                                    manager.id,
                                                    manager.is_pending
                                                        ? manager.email
                                                        : manager.fullname
                                                )
                                            }
                                            className="text-red-400 hover:text-red-300 transition-colors"
                                            title={
                                                manager.is_pending
                                                    ? "Cancel invitation"
                                                    : "Remove host"
                                            }
                                        >
                                            <XMarkIcon className="w-5 h-5" />
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            <form onSubmit={handleSubmit} className="mt-6">
                <InputLabel htmlFor="host-email" value="Invite by Email" />
                <div className="flex space-x-2">
                    <div className="flex-1">
                        <TextInput
                            id="host-email"
                            name="email"
                            type="email"
                            value={email}
                            placeholder="host@example.com"
                            onChange={(e) => setEmail(e.target.value)}
                            disabled={processing}
                        />
                        <InputError message={errors.email} />
                    </div>
                    <PrimaryButton disabled={processing}>
                        {processing ? "Inviting..." : "Invite Host"}
                    </PrimaryButton>
                </div>
                <Instruction>
                    Enter an email address to invite someone as a host. If they
                    already have an account, they'll be added immediately.
                    Otherwise, they'll receive an invitation when they sign up.
                </Instruction>
            </form>
        </div>
    );
};
