export const TagItem = ({ tag }: { tag: string }) => {
    return (
        <li className="rounded-full border border-white text-xxs font-medium capitalize shadow me-2 px-2.5 py-0.5">
            {tag}
        </li>
    );
};
