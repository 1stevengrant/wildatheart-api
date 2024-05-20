import React from "react";

type EmptyLayoutBodyProps = {
    children: React.ReactNode;
};

export const EmptyLayoutBody = ({ children }: EmptyLayoutBodyProps) => {
    return <main className={`pt-[73px] pb-[63px] px-4`}>{children}</main>;
};
