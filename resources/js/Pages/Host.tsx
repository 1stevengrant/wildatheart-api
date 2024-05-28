import React from "react";

import { UpdateProfileForm } from "@/Components/Forms/UpdateProfileForm";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { LeftColumn } from "@/Layouts/LeftColumn";
import { RightColumn } from "@/Layouts/RightColumn";
import { TwoColumnWrapper } from "@/Layouts/TwoColumnWrapper";

const Host = () => {
    const page = useTypedPage();
    return (
        <EmptyLayout title={`Update Profile Information`}>
            <TwoColumnWrapper>
                <LeftColumn>
                    <div className={`max-w-[500px] mx-auto`}>
                        <HeadingOne className={`mb-8`}>
                            We're stoked you want to start a Fire! Just a few
                            questions.
                        </HeadingOne>
                        <UpdateProfileForm user={page.props.auth.user!} />
                    </div>
                </LeftColumn>
                <RightColumn className={`bg-campfire bg-cover bg-center`}>
                    <></>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default Host;
