export const CircleIcon = ({ className = "" }: { className?: string }) => {
    return (
        <svg
            viewBox="0 0 12 12"
            className={`${className} w-3 h-3`}
            xmlns="http://www.w3.org/2000/svg"
        >
            <rect
                x="11"
                y="1"
                width="10"
                height="10"
                rx="5"
                transform="rotate(90 11 1)"
            />
        </svg>
    );
};
