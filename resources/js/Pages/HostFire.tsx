import React from 'react';
import useTypedPage from "@/Hooks/useTypedPage";
import FireInformation from "@/Components/Fires/FireInformation";
import EmptyLayout from "@/Layouts/EmptyLayout";

const Host = () => {
    const page = useTypedPage();
    return (
        <EmptyLayout title={`Your Fire Information`}>
            <FireInformation user={page.props.auth.user!}/>
        </EmptyLayout>
    )
}

export default Host;
