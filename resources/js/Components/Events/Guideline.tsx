import React from "react";

const Guideline = ({
    icon,
    title,
    description,
}: {
    icon: any;
    title: string;
    description: string;
}) => {
    return (
        <div className="flex items-center space-x-8">
            {icon}
            <div>
                <span className="font-semibold">{title}</span>
                <br />
                {description}
            </div>
        </div>
    );
};

export default Guideline;
