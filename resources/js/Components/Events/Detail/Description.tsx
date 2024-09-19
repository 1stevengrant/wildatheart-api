import React from "react";

import Markdown from "react-markdown";

import { Event } from "@/types/Event";

export const Description = ({ event }: { event: Event }) => {
    return (
        <Markdown
            className={`mt-4 prose prose-p:text-white prose-a:text-orange max-w-none prose-hr:bg-black prose-h2:text-white prose-h3:text-white  prose-li:text-white`}
        >
            {event.eventTypeData.description}
        </Markdown>
    );
};
