import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import HeadingOne from "@/Components/Type/HeadingOne";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { LeftColumn } from "@/Layouts/LeftColumn";
import { RightColumn } from "@/Layouts/RightColumn";
import { TwoColumnWrapper } from "@/Layouts/TwoColumnWrapper";

const HostAgreementPage = () => {
    return (
        <EmptyLayout title={`Host Agreement`}>
            <TwoColumnWrapper>
                <LeftColumn>
                    <div>
                        <HeadingOne>
                            Last thing, <br /> the host agreement
                        </HeadingOne>
                    </div>
                    <PrimaryButton>Agree and continue</PrimaryButton>
                </LeftColumn>
                <RightColumn className={`bg-campfire`}>
                    <></>
                </RightColumn>
            </TwoColumnWrapper>
        </EmptyLayout>
    );
};

export default HostAgreementPage;
