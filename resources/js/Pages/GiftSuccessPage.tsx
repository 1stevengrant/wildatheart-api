import React from "react";

import { GiftComing } from "@/Components/GiftComing";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { LeftColumn } from "@/Components/Layout/LeftColumn";
import { RightColumn } from "@/Components/Layout/RightColumn";
import { TwoColumnWrapper } from "@/Components/Layout/TwoColumnWrapper";

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
