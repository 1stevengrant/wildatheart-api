export const FireIcon = ({ className = "", size = "h-6 w-6" }) => {
    return (
        <svg
            className={`${className} ${size}`}
            xmlns="http://www.w3.org/2000/svg"
            width="31"
            height="31"
            fill="none"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.881"
                d="m7.445 26.625 16.9-7.771M16.503 16.93c4.029-.664 5.44-5.237 3.686-8.65-.921-1.917-2.488-3.263-4.337-4.23.282 2.17-.268 5.845-2.323 5.845-.5.21-.82-2.59-.82-2.952-.243.174-.826.715-1.039.925-3.963 3.776-.444 10.218 4.833 9.063Z"
            />
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.881"
                d="M17.383 12.132s-.619 1.452-2.367 1.183M11.52 20.729l-4.075-1.874m16.901 7.77-4.837-2.224"
            />
        </svg>
    );
};
