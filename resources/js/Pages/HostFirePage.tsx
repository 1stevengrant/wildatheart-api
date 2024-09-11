import React from "react";

import { FireInformation } from "@/Components/Events/FireInformation";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const HostFirePage = () => {
    const page = useTypedPage();
    return (
        <EmptyLayout title={`Your Fire Information`}>
            <FireInformation user={page.props.auth.user!} />
        </EmptyLayout>
    );
};

export default HostFirePage;
