import React from "react";

import { UpdateProfileForm } from "@/Components/Forms/UpdateProfileForm";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import useTypedPage from "@/Hooks/useTypedPage";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";

const MyProfilePage = () => {
    const pageTitle = `My Profile`;
    const page = useTypedPage();
    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>
            <UpdateProfileForm
                user={page.props.auth.user!}
                buttonText={"Save"}
                className={`max-w-2xl`}
            />
        </HostDashboardLayout>
    );
};

export default MyProfilePage;
