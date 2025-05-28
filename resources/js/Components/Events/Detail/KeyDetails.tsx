import { HeadingOne } from "@/Components/Type/HeadingOne";

type KeyDetailItem = {
    id: string;
    icon: string;
    type: string;
    headline: string;
    description: string;
    fields: {
        icon: string;
        headline: string;
        description: string;
    };
};
export const KeyDetails = ({ event }: { event: App.Data.EventData }) => {
    return (
        <>
            {event.event_type_data.key_details &&
                event.event_type_data.key_details.length > 0 && (
                    <div>
                        <HeadingOne className={`mt-[48px]`}>
                            Key Details
                        </HeadingOne>
                        <ul className={`my-8`}>
                            {event.event_type_data.key_details.map(
                                (item: KeyDetailItem) => (
                                    <li
                                        className="mb-4 flex items-start"
                                        key={item.id}
                                    >
                                        <div
                                            dangerouslySetInnerHTML={{
                                                __html: item.icon,
                                            }}
                                            className={`w-6 h-6 text-white`}
                                        />
                                        <div className="ml-[24px] leading-[24px]">
                                            <p className="font-medium">
                                                {item.headline}
                                            </p>
                                            <p className="text-sm text-n80">
                                                {item.description}
                                            </p>
                                        </div>
                                    </li>
                                )
                            )}
                        </ul>
                    </div>
                )}
        </>
    );
};
