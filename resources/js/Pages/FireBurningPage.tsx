import { EmptyLayout } from "@/Layouts/EmptyLayout";
import HeadingOne from "@/Components/Type/HeadingOne";
import { CenterContainer } from "@/Components/Pages/CenterContainer";
import { usePageContent } from "@/Hooks/usePageContent";
import { ContentBlocksComponent } from "@/Components/ContentBlocksComponent";

const FireBurningPage = () => {
    const { contentBlocks } = usePageContent();
    return (
        <EmptyLayout title={`Fire is burning`}>
            <CenterContainer>
                <HeadingOne>Fire is still burning</HeadingOne>
                {contentBlocks && (
                    <ContentBlocksComponent className={`mt-6`} contentBlocks={contentBlocks} />
                )}
            </CenterContainer>
        </EmptyLayout>
    );
};

export default FireBurningPage;
