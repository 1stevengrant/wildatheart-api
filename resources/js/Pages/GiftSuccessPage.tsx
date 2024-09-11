import React from "react";

import { GiftComing } from "@/Components/GiftComing";
import { LeftColumn } from "@/Components/Layout/Elements/LeftColumn";
import { RightColumn } from "@/Components/Layout/Elements/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/Elements/TwoColumnWrapper";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

const GiftSuccessPage = () => {
    return (
        <EmptyLayout title={"Claim Your Gift"}>
            <TwoColumnWrapper>
                <LeftColumn>
                    <GiftComing />
                </LeftColumn>
                <RightColumn>
                    <div className={`mx-auto`}>
                        <img
                            src="/img/fires-gift-image-v2.webp"
                            className={`max-h-[700px] hidden md:block`}
                            alt=""
                        />
                    </div>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default GiftSuccessPage;
