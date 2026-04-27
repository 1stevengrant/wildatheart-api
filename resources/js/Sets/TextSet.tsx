import { CustomMarkdown } from "@/Components/CustomMarkdown";
import { proseClasses } from "@/helpers/proseClasses";

export const TextSet = ({
    text,
    className = "",
}: {
    text?: string;
    className?: string;
}) => {
    return (
        <div className={`${className} ${proseClasses}`}>
            <CustomMarkdown>{text}</CustomMarkdown>
        </div>
    );
};
