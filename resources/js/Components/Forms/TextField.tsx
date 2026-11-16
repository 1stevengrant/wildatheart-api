import { InputHTMLAttributes } from "react";

import { InputError } from "@/Components/Forms/InputError";
import { InputLabel } from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";

type TextFieldProps = Omit<
    InputHTMLAttributes<HTMLInputElement>,
    "id" | "value" | "onChange"
> & {
    id: string;
    label?: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    isFocused?: boolean;
};

export const TextField = ({
    id,
    label,
    required,
    value,
    onChange,
    error,
    className,
    ...props
}: TextFieldProps) => {
    return (
        <div className={className}>
            {label && (
                <InputLabel htmlFor={id} value={label} required={required} />
            )}
            <TextInput
                id={id}
                name={id}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                {...props}
            />
            <InputError message={error} />
        </div>
    );
};
