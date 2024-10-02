import { HTMLAttributes } from "react";

export const InputError = ({
    message,
    className = "",
    ...props
}: HTMLAttributes<HTMLParagraphElement> & { message?: string }) => {
    return message ? (
        <div
            {...props}
            className={`field-validation-error text-sm text-red mt-1 italic ${className}`}
        >
            {message}
        </div>
    ) : null;
};
