import React from "react";
import MainHeader from "@/Components/MainHeader";
import Footer from "@/Components/Footer";

const AppLayout = ({children}: { children?: JSX.Element[] | JSX.Element }) => {
    return (
        <div className="flex flex-col h-screen">
            <MainHeader/>
            <main className={'flex-1 overflow-y-auto'}>{children}</main>
            <Footer />
        </div>
    )
}

export default AppLayout
