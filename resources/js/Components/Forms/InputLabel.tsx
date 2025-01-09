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
            className={`block font-semibold text-n70 ` + className}
        >
            {value ? value : children}
            {required ? <span className="text-r200"> *</span> : null}
        </label>
    );
};
