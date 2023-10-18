import React from "react";

const MetaItem = ({icon, title, description}: { icon: any, title: string, description: string }) => {
    return (
        <div className="flex items-start">
            {icon}
            <div className="ml-[24px] leading-[24px]">
                <p className="font-medium">{title}</p>
                <p className="text-sm text-[#B8B8B8]">
                    {description}
                </p>
            </div>
        </div>
    )
}

export default MetaItem;
