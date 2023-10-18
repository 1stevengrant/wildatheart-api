import { ButtonHTMLAttributes } from 'react';
import primaryButtonClasses from "@/helpers/primaryButtonClasses";

export default function PrimaryButton({ className = '', disabled, children, ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `${primaryButtonClasses} ${
                    disabled && ''
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
