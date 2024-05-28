export const CircleIcon = ({ fill }: { fill?: string }) => {
    return (
        <svg
            width="12"
            height="12"
            viewBox="0 0 12 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <rect
                x="11"
                y="1"
                width="10"
                height="10"
                rx="5"
                transform="rotate(90 11 1)"
                fill={fill || "black"}
                stroke="white"
            />
        </svg>
    );
};
