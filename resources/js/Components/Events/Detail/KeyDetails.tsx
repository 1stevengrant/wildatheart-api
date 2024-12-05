import { HeadingOne } from "@/Components/Type/HeadingOne";
import { KeyDetailItem } from "@/interfaces/KeyDetailItem";
import { Event } from "@/types/Event";

export const KeyDetails = ({ event }: { event: Event }) => {
    return (
        <>
            {event.eventTypeData.key_details &&
                event.eventTypeData.key_details.length > 0 && (
                    <div>
                        <HeadingOne className={`mt-[48px]`}>
                            Key Details
                        </HeadingOne>
                        <ul className={`my-8`}>
                            {event.eventTypeData.key_details.map(
                                (item: KeyDetailItem) => (
                                    <li
                                        className="mb-4 flex items-start"
                                        key={item.id} // Changed from index to item.id
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
                                            <p className="text-sm text-[#B8B8B8]">
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
