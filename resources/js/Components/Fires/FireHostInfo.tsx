import React from "react";
type FireHostInfoProps = {
    children: React.ReactNode;
}
const FireHostInfo = ({children}: FireHostInfoProps) => {
    return (
        <div className={'flex items-center space-x-2'}>
            {children}
        </div>
    )
}

export default FireHostInfo
