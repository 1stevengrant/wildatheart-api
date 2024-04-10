import React from "react";

interface Heading1Props {
    children: React.ReactNode;
    className?: string;
}

const HeadingOne = ({ children, className = "" }: Heading1Props) => {
    return (
        <h1
            className={`text-xl md:text-3xl leading-tight md:leading-normal text-white ${className}`}
        >
            {children}
        </h1>
    );
};

export default HeadingOne;
