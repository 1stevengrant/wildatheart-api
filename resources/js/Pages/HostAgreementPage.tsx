import React, { useState } from "react";

import { router } from "@inertiajs/react";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import { SwitchComponent } from "@/Components/SwitchComponent";
import HeadingOne from "@/Components/Type/HeadingOne";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { LeftColumn } from "@/Layouts/LeftColumn";
import { RightColumn } from "@/Layouts/RightColumn";
import { TwoColumnWrapper } from "@/Layouts/TwoColumnWrapper";

const HostAgreementPage = () => {
    const handleAgree = (event: React.FormEvent) => {
        event.preventDefault();
        router.post(route("host.agreement.store"), {
            preserveScroll: true,
        });
    };

    const [hasAgreed, setHasAgreed] = useState(false);

    return (
        <EmptyLayout title={`Host Agreement`}>
            <TwoColumnWrapper>
                <LeftColumn>
                    <HeadingOne>
                        Last thing, <br /> the host agreement
                    </HeadingOne>
                    <div className={`prose prose-p:text-white`}>
                        <p>
                            Bacon ipsum dolor sit amet rump turkey spare ribs
                            shank, hamburger boudin frankfurter ham hock
                            pancetta salami. Jerky porchetta salami, fatback
                            swine chicken pastrami beef ribs landjaeger biltong.
                            Capicola prosciutto leberkas landjaeger. Flank
                            turducken chuck pork loin drumstick tongue ground
                            round ham hock fatback short ribs pancetta turkey.
                            Shank beef ribs frankfurter ribeye cow fatback swine
                            kielbasa biltong. Pork pork chop prosciutto pig.
                            Shoulder andouille hamburger sausage. Andouille
                            tri-tip chicken kevin shankle shank, pancetta
                            hamburger t-bone. Pancetta short loin meatloaf beef
                            ribs. Turkey chuck strip steak rump brisket meatloaf
                            tenderloin venison jowl pig hamburger sausage flank
                            corned beef. Drumstick hamburger filet mignon
                            chicken, porchetta ground round swine rump turkey
                            strip steak.
                        </p>
                        <label
                            className={`flex items-center space-x-2 hover:cursor-pointer`}
                        >
                            <SwitchComponent
                                checked={hasAgreed}
                                onChange={() => setHasAgreed(!hasAgreed)}
                            />
                            <p>I agree with the Wild at Heart Host Agreement</p>
                        </label>
                    </div>
                    <PrimaryButton disabled={!hasAgreed} onClick={handleAgree}>
                        Agree and continue
                    </PrimaryButton>
                </LeftColumn>
                <RightColumn className={`bg-campfire`}>
                    <></>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default HostAgreementPage;
