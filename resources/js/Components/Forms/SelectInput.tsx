import { ComponentProps } from "react";

interface SelectInputProps extends ComponentProps<"select"> {
    error?: string;
    options: { value: string | number; label: string }[];
}

export default function SelectInput({
    name,
    error,
    className = "",
    options = [],
    ...props
}: SelectInputProps) {
    return (
        <select
            id={name}
            name={name}
            {...props}
            className={`bg-n900 rounded-full pl-6 focus:ring-p200 focus:outline-none focus:border-none ${className} ${error ? "" : ""}`}
        >
            {options?.map(({ value, label }, index) => (
                <option key={index} value={value}>
                    {label}
                </option>
            ))}
        </select>
    );
}
