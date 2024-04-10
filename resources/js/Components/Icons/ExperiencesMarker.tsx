import React from "react";

const ExperiencesMarker = ({ className = "" }) => {
    return (
        <svg
            className={className}
            xmlns="http://www.w3.org/2000/svg"
            width="23"
            height="19"
            fill="none"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.6"
                d="M5.547 1.667h11.666a4.727 4.727 0 0 1 4.727 4.727v6.402a4.727 4.727 0 0 1-4.727 4.728H5.547A4.727 4.727 0 0 1 .82 12.796V6.394a4.727 4.727 0 0 1 4.727-4.727Z"
            />
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.6"
                d="M13.81 10.668a9.376 9.376 0 0 1-2.747 1.724c-.886.35-1.629-.087-1.738-.961a17.227 17.227 0 0 1 0-3.673c.12-.908.937-1.294 1.738-.957 1.027.42 1.947.991 2.747 1.724.684.62.7 1.5 0 2.143Z"
                clipRule="evenodd"
            />
        </svg>
    );
};

export default ExperiencesMarker;
