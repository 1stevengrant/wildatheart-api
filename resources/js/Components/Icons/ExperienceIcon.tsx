import React from "react";

interface ExperienceIconProps {
    className?: string;
}

const ExperienceIcon = ({ className = "" }: ExperienceIconProps) => {
    return (
        <svg
            className={`${className}`}
            xmlns="http://www.w3.org/2000/svg"
            width="32"
            height="32"
            fill="none"
        >
            <path
                stroke="#fff"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.92"
                d="M10.062 5.32H22.79a5.157 5.157 0 0 1 5.157 5.157v6.984a5.157 5.157 0 0 1-5.157 5.158H10.062a5.157 5.157 0 0 1-5.156-5.158v-6.984a5.157 5.157 0 0 1 5.156-5.157Z"
            />
            <path
                stroke="#fff"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.92"
                d="M19.07 15.14a10.228 10.228 0 0 1-2.996 1.88c-.966.381-1.777-.095-1.897-1.049a18.793 18.793 0 0 1 0-4.006c.131-.99 1.023-1.411 1.897-1.044a10.009 10.009 0 0 1 2.996 1.88c.747.677.764 1.637 0 2.338Z"
                clipRule="evenodd"
            />
        </svg>
    );
};

export default ExperienceIcon;
