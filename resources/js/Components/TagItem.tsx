import React from "react";

export const TagItem = ({ tag }: { tag: string }) => {
    return (
        <li className="text-xs font-medium me-2 px-2.5 py-0.5 rounded-full border border-white shadow capitalize">
            {tag}
        </li>
    );
};
