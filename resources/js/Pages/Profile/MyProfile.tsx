import React from "react";
import AppLayout from "@/Layouts/AppLayout";
import MyProfileWrapper from "@/Layouts/MyProfileWrapper";
import UpdateProfileForm from "@/Components/Forms/UpdateProfileForm";
import useTypedPage from "@/Hooks/useTypedPage";

const MyProfile = () => {
    const pageTitle = `My Profile`;
    const page = useTypedPage();
    return (
        <AppLayout title={pageTitle}>
            <MyProfileWrapper title={pageTitle}>
                <UpdateProfileForm
                    user={page.props.auth.user!}
                    redirectTo={route('my.profile')}
                    buttonText={'Save'}
                    className={`max-w-2xl`}/>
            </MyProfileWrapper>
        </AppLayout>
    )
}

export default MyProfile;
