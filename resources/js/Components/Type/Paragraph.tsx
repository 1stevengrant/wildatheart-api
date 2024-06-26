export const Paragraph = ({ item }: { item: any }) => {
    return (
        <p>
            {item.content.map((contentItem: any, contentIndex: number) => (
                <span key={contentIndex}>{contentItem.text}</span>
            ))}
        </p>
    );
};
