import React from "react";

import { UpdateProfileForm } from "@/Components/Forms/UpdateProfileForm";
import { MyProfileWrapper } from "@/Components/Layout/Elements/MyProfileWrapper";
import useTypedPage from "@/Hooks/useTypedPage";
import { AppLayout } from "@/Layouts/AppLayout";

const MyProfilePage = () => {
    const pageTitle = `My Profile`;
    const page = useTypedPage();
    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                <UpdateProfileForm
                    user={page.props.auth.user!}
                    redirectTo={route("my.profile")}
                    buttonText={"Save"}
                    className={`max-w-2xl`}
                />
            </MyProfileWrapper>
        </AppLayout>
    );
};

export default MyProfilePage;
