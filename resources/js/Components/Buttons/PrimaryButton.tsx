import { ButtonHTMLAttributes } from "react";

import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";

export const PrimaryButton = ({
    className = "",
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) => {
    return (
        <button
            {...props}
            className={`${primaryButtonClasses} ${disabled && ""} ` + className}
            disabled={disabled}
        >
            {children}
        </button>
    );
};
