import React from "react";

export const ListItem = ({
    icon,
    text,
    url,
}: {
    icon: React.ReactNode;
    text: string;
    url: string;
}) => {
    return (
        <li className={`leading-normal`}>
            <a
                href={url}
                className={`flex items-center hover:text-orange transition duration-150 ease-in-out`}
            >
                {icon}
                <div>{text}</div>
            </a>
        </li>
    );
};
