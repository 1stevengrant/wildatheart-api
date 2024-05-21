import { XMarkIcon } from "@heroicons/react/24/solid";
import { usePage } from "@inertiajs/react";
import { useRecoilState } from "recoil";

import Logo from "@/Components/Logo";
import { imageGenerator } from "@/helpers/user";
import { eventCreateProgressState } from "@/store/uiAtom";

type EventStepProps = {
    step: number;
};
const EventStep = ({ step }: EventStepProps) => {
    return <div className={`text-xs text-grey-200`}>Step {step}</div>;
};

const EventStepBar = () => {
    return <div className={`rounded h-[50px] bg-white w-[5px]`} />;
};

export const EventCreateProgress = () => {
    const [eventCreateProgressShow, setEventCreateProgressShow] =
        useRecoilState(eventCreateProgressState);
    const { eventType } = usePage().props as any;

    return (
        <div
            className={`z-50 p-4 absolute top-0 left-0 bg-black h-[100dvh] w-full ${eventCreateProgressShow ? `block` : `hidden`}`}
        >
            <div className={`flex justify-between`}>
                <Logo
                    className={
                        "w-[125px] flex-shrink-0 md:w-[100px] lg:w-[150px] mr-2"
                    }
                />
                <XMarkIcon
                    onClick={() => setEventCreateProgressShow(false)}
                    className={`w-8 h-8`}
                />
            </div>
            {eventType && (
                <>
                    <div
                        className={`border-2 p-2 border-grey-border rounded-lg mt-4 flex items-center`}
                    >
                        <div>
                            <img
                                className={`rounded-lg`}
                                src={imageGenerator(eventType.image, {
                                    width: 100,
                                    height: 100,
                                })}
                                alt={eventType.name}
                            />
                        </div>
                        <div className={`ml-6`}>
                            <h2 className={`text-sm font-medium`}>
                                {eventType.name}
                            </h2>
                            <p className={`text-sm`}>Event setup</p>
                        </div>
                    </div>
                    <ul className={`ml-10 mt-10 space-y-6`}>
                        <li>
                            <div className={`flex`}>
                                <EventStepBar />
                                <div className={`ml-6`}>
                                    <EventStep step={1} />
                                    <div className={`font-medium`}>
                                        Event Guide
                                    </div>
                                </div>
                            </div>
                            <ul className={`text-sm mt-4 ml-14 space-y-2`}>
                                <li>Welcome</li>
                                <li>Before Your {eventType.name}</li>
                                <li>{eventType.name} Schedule</li>
                            </ul>
                        </li>
                        <li className={`flex`}>
                            <EventStepBar />
                            <div className={`ml-6`}>
                                <EventStep step={2} />
                                <div className={`font-medium`}>
                                    Mailing Address
                                </div>
                            </div>
                        </li>
                        <li className={`flex`}>
                            <EventStepBar />
                            <div className="ml-6">
                                <EventStep step={3} />
                                <div className={`font-medium`}>
                                    Event Registration
                                </div>
                            </div>
                        </li>
                    </ul>
                </>
            )}
        </div>
    );
};
