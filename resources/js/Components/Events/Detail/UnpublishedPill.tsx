import React from "react";
import { Event } from "@/types/Event";

export const UnpublishedPill = ({ event }: { event: Event }) => {
    if (!event.approved_at) {
        return (
            <span className="ml-2 rounded px-2 py-1 text-sm font-semibold uppercase text-white bg-orange">
                Unpublished
            </span>
        );
    }
};
