import Markdown from "react-markdown";

export const TextSet = ({
    text,
    className = "",
}: {
    text: string;
    className?: string;
}) => {
    return <Markdown className={className}>{text}</Markdown>;
};
