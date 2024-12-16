import { Link } from "@inertiajs/react";
import classNames from "classnames";

interface PaginationProps {
    links: PaginationItem[];
}

export default function Pagination({ links = [] }: PaginationProps) {
    /**
     * If there are only 3 links, it means there are no previous or next pages.
     * So, we don't need to render the pagination.
     */
    if (links.length === 3) return null;

    return (
        <div className="flex flex-wrap mt-6 -mb-1">
            {links?.map((link) => {
                return link?.url === null ? (
                    <PageInactive key={link.label} label={link.label} />
                ) : (
                    <PaginationItem key={link.label} {...link} />
                );
            })}
        </div>
    );
}

interface PaginationItem {
    url: null | string;
    label: string;
    active: boolean;
}

function PaginationItem({ active, label, url }: PaginationItem) {
    const className = classNames(
        [
            "mr-1 mb-1",
            "px-4 py-3",
            "border-2 border-n400 rounded",
            "text-sm font-semibold",
            "hover:border-white",
            "focus:outline-none focus:border-p200 focus:text-p200",
        ],
        {
            "bg-black border-p200 text-p200": active,
        }
    );

    /**
     * Note: In general you should be aware when using `dangerouslySetInnerHTML`.
     *
     * In this case, `label` from the API is a string, so it's safe to use it.
     * It will be either `&laquo; Previous` or `Next &raquo;`
     */
    return (
        <Link className={className} href={url as string}>
            <span dangerouslySetInnerHTML={{ __html: label }}></span>
        </Link>
    );
}

function PageInactive({ label }: Pick<PaginationItem, "label">) {
    const className = classNames(
        "mr-1 mb-1 px-4 py-3 text-sm font-semibold border-2 rounded border-solid border-n400 text-n400 cursor-not-allowed"
    );

    /**
     * Note: In general you should be aware when using `dangerouslySetInnerHTML`.
     *
     * In this case, `label` from the API is a string, so it's safe to use it.
     * It will be either `&laquo; Previous` or `Next &raquo;`
     */
    return (
        <div
            className={className}
            dangerouslySetInnerHTML={{ __html: label }}
        />
    );
}
