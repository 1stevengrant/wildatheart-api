export const Heading = ({ item }: { item: any }) => {
    const HeadingTag = `h${item.attrs.level}` as keyof JSX.IntrinsicElements;
    return (
        <HeadingTag>
            {item.content.map((contentItem: any, contentIndex: number) => (
                <span key={contentIndex}>{contentItem.text}</span>
            ))}
        </HeadingTag>
    );
};
