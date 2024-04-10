import React from "react";

import FireMarker from "@/Components/Events/FireMarker";
import Logo from "@/Components/Logo";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const SuccessfulCallbackPage = () => {
    return (
        <EmptyLayout title={"Check your email"}>
            <div className="flex h-screen flex-col items-center justify-center">
                <div className="text-center text-2xl font-semibold text-white">
                    <div className="flex items-center">
                        <Logo className={`mx-auto mb-4 w-[300px]`} />
                        <FireMarker className={`mx-auto mb-4 h-[200px]`} />
                    </div>
                    <h2>Please check your email for your login link</h2>
                </div>
            </div>
        </EmptyLayout>
    );
};

export default SuccessfulCallbackPage;
