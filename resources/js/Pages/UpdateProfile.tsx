import React from "react";
import TwoColumnWrapper from "@/Layouts/TwoColumnWrapper";
import LeftColumn from "@/Layouts/LeftColumn";
import HeadingOne from "@/Components/Type/HeadingOne";
import RightColumn from "@/Layouts/RightColumn";
import UpdateProfileForm from "@/Components/Forms/UpdateProfileForm";
import useTypedPage from "@/Hooks/useTypedPage";

const UpdateProfile = () => {
    const page = useTypedPage();
    return (
        <TwoColumnWrapper>
            <LeftColumn className={''}>
                <div className={'mx-auto max-w-[500px]'}>
                    <HeadingOne className={'my-12'}>
                        We're stoked you want to start a Fire! Just a few questions.
                    </HeadingOne>
                    <p>One of the most important aspects of our Fires are the hosts that commit to them.</p>
                    <UpdateProfileForm user={page.props.auth.user!}
                                       buttonText={'Next'}
                                       redirectTo={route('host.fire')}
                    />
                </div>
            </LeftColumn>
            <RightColumn className={'bg-campfire bg-cover bg-center'}>
                <></>
            </RightColumn>
        </TwoColumnWrapper>
    )
}

export default UpdateProfile;
