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
            className={
                "p-2 border border-grey-border w-full bg-form rounded-md focus:border-orange focus:outline-none" +
                " focus:ring-orange text-white " +
                className
            }
            ref={localRef}
        ></textarea>
    );
});
