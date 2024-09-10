import React from "react";

export const TagGroup = ({ children }: { children: React.ReactNode }) => {
    return <ul className={`flex items-center`}>{children}</ul>;
};
