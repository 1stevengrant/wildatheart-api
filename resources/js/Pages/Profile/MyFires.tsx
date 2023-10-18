import React from "react";
import AppLayout from "@/Layouts/AppLayout";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";
import {Link, usePage} from "@inertiajs/react";
import {Fire} from "@/types/Fire";
import FireAdminCard from "@/Components/Fires/FireAdminCard";
import type { PageProps} from '@inertiajs/core'
import primaryButtonClasses from "@/helpers/primaryButtonClasses";

const MyFires = () => {
    const pageTitle = `My Fires`;
    // @ts-ignore
    const {campfires: {data: fires}} = usePage().props as PageProps;

    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>

                {fires.length > 0 ? (
                    <div className={`grid gap-6 md:grid-cols-2 clear-both`}>
                        {fires.map((fire: Fire, index: number) => (
                            <FireAdminCard fire={fire} key={index}/>
                        ))}
                    </div>
                ) : (
                    <p className="text-center text-2xl clear-both">
                        You have no Fires currently
                    </p>
                )}

                <div className="flex items-center justify-center mt-6">
                    <Link href={route('host')}
                          className={`${primaryButtonClasses}`}>
                        Start a New Fire
                    </Link>
                </div>

            </MyProfileWrapper>
        </AppLayout>
    )
}

export default MyFires;
