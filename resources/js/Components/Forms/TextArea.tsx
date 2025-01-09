import {
    forwardRef,
    useEffect,
    useImperativeHandle,
    useRef,
    TextareaHTMLAttributes,
} from "react";

export default forwardRef(function Textarea(
    {
        className = "",
        isFocused = false,
        ...props
    }: TextareaHTMLAttributes<HTMLTextAreaElement> & { isFocused?: boolean },
    ref
) {
    const localRef = useRef<HTMLTextAreaElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, []);

    return (
        <textarea
            {...props}
            className={`p-2 border border-n400 w-full bg-n900 rounded-md focus:border-p200 focus:outline-none
                focus:ring-p200 text-white block ${className}`}
            ref={localRef}
        ></textarea>
    );
});
