import React from "react";
type FireHostInfoProps = {
    children: React.ReactNode;
}
const FireInfo = ({children}: FireHostInfoProps) => {
    return (
        <div className={'flex flex-col justify-between relative'}>
            {children}
        </div>
    )
}

export default FireInfo
