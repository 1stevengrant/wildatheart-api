import React from "react";

export const ListItem = ({
    icon,
    text,
    url,
    openInNewTab = false,
}: {
    icon: React.ReactNode;
    text: string;
    url: string;
    openInNewTab?: boolean;
}) => {
    return (
        <li className={`leading-tight`}>
            <a
                target={openInNewTab ? "_blank" : "_self"}
                href={url}
                className={`flex items-center hover:text-p200 transition duration-150 ease-in-out`}
            >
                {icon}
                <div>{text}</div>
            </a>
        </li>
    );
};
