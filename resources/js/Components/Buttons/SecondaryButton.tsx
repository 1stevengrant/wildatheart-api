import { ButtonHTMLAttributes } from "react";

export default function SecondaryButton({
    className = "",
    disabled,
    children,
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement>) {
    return (
        <button
            {...props}
            className={
                `px-2 py-1 md:px-4 md:py-2  text-sm md:text-base border
        border-transparent rounded-md bg-white hover:bg-white/80 text-black text-center font-medium transition
        ease-in-out duration-150 ${disabled && "opacity-25"} ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
