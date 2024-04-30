import { usePage } from "@inertiajs/react";


type PageContent = {
    title: string;
    slug: string;
    content: string[];
};

export const usePageContent = () => {
    const page = usePage();
    const pageContent = page.props.pageContent as PageContent;
    const contentBlocks = pageContent.content;

    return {
        contentBlocks,
    };
};
