import { useSwipeable } from "react-swipeable";
import { useRecoilState } from "recoil";

import { EventCardMobile } from "@/Components/Events/EventCardMobile";
import { useOnClickOutside } from "@/Hooks/useOnClickOutside";
import { currentEventState } from "@/store/eventsAtom";

export const OutsideClickHandlerEvents = () => {
    const [currentEvent, setCurrentEvent] = useRecoilState(currentEventState);
    const ref = useOnClickOutside(() => setCurrentEvent(undefined));
    const swipeHandlers = useSwipeable({
        onSwipedDown: () => setCurrentEvent(undefined),
        onSwipedUp: () => setCurrentEvent(undefined),
        onSwipedLeft: () => setCurrentEvent(undefined),
        onSwipedRight: () => setCurrentEvent(undefined),
        swipeDuration: 500,
        preventScrollOnSwipe: true,
        trackMouse: true,
    });

    return (
        <>
            {currentEvent && (
                <div ref={ref}>
                    <div {...swipeHandlers}>
                        <EventCardMobile
                            className={`fixed bottom-4 right-4 left-4 lg:hidden z-[1001] md:max-w-[500px] md:mx-auto`}
                        />
                    </div>
                </div>
            )}
        </>
    );
};
