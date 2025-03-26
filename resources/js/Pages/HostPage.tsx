import React from "react";

import { UpdateProfileForm } from "@/Components/Forms/UpdateProfileForm";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const HostPage = () => {
    const page = useTypedPage();

    return (
        <EmptyLayout title={`Update Profile Information`}>
            <TwoColumnWrapper>
                <LeftColumn>
                    <div className={`max-w-[500px] mx-auto`}>
                        <HeadingOne className={`mb-8`}>
                            We're stoked you want to host an event! Just a few
                            questions.
                        </HeadingOne>
                        <UpdateProfileForm
                            redirectTo={route("host.eventType.select")}
                            user={page.props.auth.user!}
                        />
                    </div>
                </LeftColumn>
                <RightColumn className={`bg-campfire bg-cover bg-center`}>
                    <></>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default HostPage;
