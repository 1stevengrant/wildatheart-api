import { EmptyLayout } from "@/Layouts/EmptyLayout";
import HeadingOne from "@/Components/Type/HeadingOne";
import { CenterContainer } from "@/Components/Pages/CenterContainer";
import { useForm, usePage } from "@inertiajs/react";
import { Event } from "@/types/Event";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import VideoPlayer from "@/Components/Events/Detail/VideoPlayer";
import { usePageContent } from "@/Hooks/usePageContent";
import { ContentBlocksComponent } from "@/Components/ContentBlocksComponent";

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
                    <ContentBlocksComponent className={`mt-6`} contentBlocks={contentBlocks} />
                )}
                <div className={` flex items-center justify-between space-x-6 mt-6`}>
                    <PrimaryButton onClick={extinguishFire}>
                        Extinguish Fire
                    </PrimaryButton>
                </div>
            </CenterContainer>
        </EmptyLayout>
    );
};

export default FireExtinguishedPage;
