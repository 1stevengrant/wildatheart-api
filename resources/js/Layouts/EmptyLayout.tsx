import React, {PropsWithChildren} from "react";
import MainHeader from "@/Components/MainHeader";
import Footer from "@/Components/Footer";
import Notification from "@/Components/Notification";
import {Head, usePage} from "@inertiajs/react";
import {useRecoilState} from "recoil";
import {reactNotificationAtom} from "@/store";

interface Props {
    title: string;
}

const EmptyLayout = ({
                       title,
                       children
                   }: PropsWithChildren<Props>) => {


    return (
        <>
            <Head title={title}/>
            {children}
        </>
    )
}

export default EmptyLayout
