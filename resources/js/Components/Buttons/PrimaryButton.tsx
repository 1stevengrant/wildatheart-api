import { ButtonHTMLAttributes } from 'react';

export default function PrimaryButton({ className = '', disabled, children, ...props }: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `px-2 py-1 md:px-4 md:py-2  text-sm md:text-base border
        border-transparent rounded-md bg-orange hover:bg-transparent-orange text-white text-center font-medium transition
        ease-in-out duration-150 disabled:bg-grey-200 disabled:text-grey-darker disabled:cursor-not-allowed ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
