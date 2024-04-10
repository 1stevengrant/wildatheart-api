import React from "react";

const MenIcon = ({ className = "" }: { className?: string }) => {
    return (
        <svg
            className={`${className}`}
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            fill="none"
        >
            <path
                stroke="#fff"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="1.696"
                d="m17.555 17.028-1.634 3.92c-.589 1.411-.883 2.117-1.244 2.403a1.784 1.784 0 0 1-2.214.003c-.361-.285-.657-.99-1.249-2.4l-1.645-3.92c-.396-.946-.594-1.418-.584-1.801a1.784 1.784 0 0 1 1.05-1.58c.35-.158.862-.158 1.887-.158H15.2c1.022 0 1.533 0 1.882.157.624.281 1.033.894 1.052 1.578.01.383-.186.854-.58 1.798ZM10.445 6.537a3.146 3.146 0 1 0 6.291 0 3.146 3.146 0 0 0-6.29 0Z"
            />
        </svg>
    );
};

export default MenIcon;
