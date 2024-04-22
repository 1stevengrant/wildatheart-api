import { Link, usePage } from "@inertiajs/react";
import Markdown from "react-markdown";

import { VideoPlayer } from "@/Components/Events/Detail/VideoPlayer";
import { ButtonContainer } from "@/Components/Page/Event/ButtonContainer";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import { FlattenedType } from "@/interfaces/FlattenedType";

const ScheduleEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    const eventTypeSchedule = JSON.parse(eventType.event_schedule);

    return (
        <PageContainer>
            <h1>Schedule {eventType.name}</h1>

            {eventTypeSchedule &&
                eventTypeSchedule.map(
                    (schedule: FlattenedType, index: number) => {
                        switch (schedule.type) {
                            case "video-block":
                                return (
                                    <VideoPlayer
                                        key={index}
                                        src={schedule.fields.video_url}
                                    />
                                );
                            case "text-block":
                                return (
                                    <Markdown key={index}>
                                        {schedule.fields.text}
                                    </Markdown>
                                );
                            default:
                                return (
                                    <span className={`unknown-block-type`} />
                                );
                        }
                    }
                )}

            <ButtonContainer>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.before", eventType)}
                >
                    Before
                </Link>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.faqs", eventType)}
                >
                    FAQs
                </Link>
            </ButtonContainer>
        </PageContainer>
    );
};

export default ScheduleEventTypePage;
