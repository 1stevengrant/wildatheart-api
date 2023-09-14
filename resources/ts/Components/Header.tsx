import React from "react";

const Header = ({children}: {children?: JSX.Element[] | JSX.Element}) => {
    return (<header
        className={
            'container mx-auto py-2 px-4 lg:px-0'
        }
    >
        <div className={''}>{children}</div>
    </header>)
}

export default Header
