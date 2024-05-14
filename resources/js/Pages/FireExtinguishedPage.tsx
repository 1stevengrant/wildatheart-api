import { useForm, usePage } from "@inertiajs/react";

import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import { ContentBlocksComponent } from "@/Components/ContentBlocksComponent";
import { CenterContainer } from "@/Components/Pages/CenterContainer";
import HeadingOne from "@/Components/Type/HeadingOne";
import { usePageContent } from "@/Hooks/usePageContent";
import { EmptyLayout } from "@/Layouts/EmptyLayout";
import { Event } from "@/types/Event";

const FireExtinguishedPage = () => {
    const page = usePage();
    const event = page.props.event as Event;
    const destroyUrl = page.props.destroyUrl as string;
    const form = useForm();
    const { contentBlocks } = usePageContent();

    const extinguishFire = async () => {
        form.post(destroyUrl, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    return (
        <EmptyLayout title={`Fire is extinguished`}>
            <CenterContainer>
                <HeadingOne>Extinguish {event.name} Fire?</HeadingOne>
                {contentBlocks && (
                    <ContentBlocksComponent
                        className={`mt-6`}
                        contentBlocks={contentBlocks}
                    />
                )}
                <div
                    className={` flex items-center justify-between space-x-6 mt-6`}
                >
                    <PrimaryButton onClick={extinguishFire}>
                        Extinguish Fire
                    </PrimaryButton>
                </div>
            </CenterContainer>
        </EmptyLayout>
    );
};

export default FireExtinguishedPage;
