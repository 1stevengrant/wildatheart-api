import { HTMLAttributes } from "react";

export default function InputError({
    message,
    className = "",
    ...props
}: HTMLAttributes<HTMLParagraphElement> & { message?: string }) {
    return message ? (
        <div {...props} className={`text-sm text-red mt-1 italic ${className}`}>
            {message}
        </div>
    ) : null;
}
