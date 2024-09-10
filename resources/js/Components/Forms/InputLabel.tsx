import { LabelHTMLAttributes } from "react";

interface ExtendedLabelAttributes
    extends LabelHTMLAttributes<HTMLLabelElement> {
    value?: string;
    required?: boolean;
}

export const InputLabel = ({
    value,
    className = "",
    children,
    required,
    ...props
}: ExtendedLabelAttributes) => {
    return (
        <label
            {...props}
            className={`block font-semibold text-sm text-grey-200 ` + className}
        >
            {value ? value : children}
            {required ? <span className="text-red"> *</span> : null}
        </label>
    );
};
