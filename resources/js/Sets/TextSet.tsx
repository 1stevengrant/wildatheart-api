import Markdown from "react-markdown";

export const TextSet = ({
    text,
    className = "",
}: {
    text?: string;
    className?: string;
}) => {
    return (
        <Markdown
            className={`${className} prose-p:text-white prose-strong:text-white prose-ol:text-white prose-ul:text-white prose-li:text-white prose-li:marker:text-white prose-headings:text-white prose-a:text-p200 hover:prose-a:no-underline`}
        >
            {text}
        </Markdown>
    );
};
