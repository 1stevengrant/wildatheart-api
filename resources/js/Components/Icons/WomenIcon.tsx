import React from "react";

const WomenIcon = ({ className = "" }: { className?: string }) => {
    return (
        <svg
            className={`${className}`}
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
        >
            <path
                stroke="#fff"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.5"
                d="m15.535 17.875-1.446-3.468c-.52-1.248-.78-1.873-1.1-2.126a1.578 1.578 0 0 0-1.959-.002c-.32.252-.58.876-1.104 2.124L8.47 17.87c-.351.836-.526 1.254-.518 1.593.016.606.377 1.149.93 1.398.309.14.762.14 1.669.14h2.9c.905 0 1.357 0 1.666-.14.552-.248.913-.79.93-1.395.01-.339-.165-.756-.513-1.591ZM9.234 5.783a2.783 2.783 0 1 0 5.566 0 2.783 2.783 0 0 0-5.566 0Z"
            />
        </svg>
    );
};

export default WomenIcon;
