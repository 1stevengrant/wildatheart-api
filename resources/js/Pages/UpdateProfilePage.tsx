import React from "react";

import { UpdateProfileForm } from "@/Components/Forms/UpdateProfileForm";
import HeadingOne from "@/Components/Type/HeadingOne";
import useTypedPage from "@/Hooks/useTypedPage";
import { LeftColumn } from "@/Layouts/LeftColumn";
import { RightColumn } from "@/Layouts/RightColumn";
import TwoColumnWrapper from "@/Layouts/TwoColumnWrapper";

const UpdateProfilePage = () => {
    const page = useTypedPage();
    return (
        <TwoColumnWrapper>
            <LeftColumn className={""}>
                <div className={"mx-auto max-w-[500px]"}>
                    <HeadingOne className={"my-12"}>
                        We're stoked you want to start a Fire! Just a few
                        questions.
                    </HeadingOne>
                    <p>
                        One of the most important aspects of our Fires are the
                        hosts that commit to them.
                    </p>
                    <UpdateProfileForm
                        user={page.props.auth.user!}
                        buttonText={"Next"}
                        redirectTo={route("host.event")}
                    />
                </div>
            </LeftColumn>
            <RightColumn className={"bg-campfire bg-cover bg-center"}>
                <></>
            </RightColumn>
        </TwoColumnWrapper>
    );
};

export default UpdateProfilePage;
