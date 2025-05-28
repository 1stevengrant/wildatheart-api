import Markdown from "react-markdown";

import { proseClasses } from "@/helpers/proseClasses";

export const TextSet = ({
    text,
    className = "",
}: {
    text?: string;
    className?: string;
}) => {
    return (
        <Markdown className={`${className} ${proseClasses}`}>{text}</Markdown>
    );
};
