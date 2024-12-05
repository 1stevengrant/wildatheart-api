import React from "react";

import { FireInformation } from "@/Components/Events/FireInformation";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const HostCaptivatingFirePage = () => {
    const page = useTypedPage();
    return (
        <EmptyLayout title={`Your Captivating Fire Information`}>
            <FireInformation user={page.props.auth.user!} />
        </EmptyLayout>
    );
};

export default HostCaptivatingFirePage;
