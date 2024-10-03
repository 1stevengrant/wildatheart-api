import React from "react";

import Markdown, { Components } from "react-markdown";

const CustomLink: Components["a"] = ({ href, children, ...props }) => (
    <a href={href} target="_blank" rel="noopener noreferrer" {...props}>
        {children}
    </a>
);
export const CustomMarkdown = ({ ...props }) => {
    return (
        <Markdown
            components={{
                a: CustomLink,
            }}
            {...props}
        />
    );
};
