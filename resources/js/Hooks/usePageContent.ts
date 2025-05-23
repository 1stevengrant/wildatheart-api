import { usePage } from "@inertiajs/react";

type PageContent = {
    title: string;
    slug: string;
    new_content: string[];
};

export const usePageContent = () => {
    const page = usePage();
    const pageContent = page.props.pageContent as PageContent;
    const contentBlocks = pageContent.new_content;

    return {
        contentBlocks,
    };
};
