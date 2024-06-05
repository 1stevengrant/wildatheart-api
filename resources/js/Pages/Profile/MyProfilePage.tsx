import React from "react";

import { UpdateProfileForm } from "@/Components/Forms/UpdateProfileForm";
import { MyProfileWrapper } from "@/Components/Layout/Elements/MyProfileWrapper";
import useTypedPage from "@/Hooks/useTypedPage";
import { AppLayout } from "@/Layouts/AppLayout";
import { HostDashboardLayout } from "@/Layouts/HostDashboardLayout";
import { HeadingOne } from "@/Components/Type/HeadingOne";

const MyProfilePage = () => {
    const pageTitle = `My Profile`;
    const page = useTypedPage();
    return (
        <HostDashboardLayout pageTitle={pageTitle}>
            <HeadingOne>{pageTitle}</HeadingOne>
            <UpdateProfileForm
                user={page.props.auth.user!}
                redirectTo={route("my.profile")}
                buttonText={"Save"}
                className={`max-w-2xl`}
            />
        </HostDashboardLayout>
    );
};

export default MyProfilePage;
