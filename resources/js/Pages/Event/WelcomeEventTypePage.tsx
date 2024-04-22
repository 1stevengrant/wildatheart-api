import { Link, usePage } from "@inertiajs/react";
import Markdown from "react-markdown";

import { VideoPlayer } from "@/Components/Events/Detail/VideoPlayer";
import { ButtonContainer } from "@/Components/Page/Event/ButtonContainer";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useRoute from "@/Hooks/useRoute";

const WelcomeEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    const route = useRoute();
    return (
        <PageContainer>
            <h1>{eventType.name}</h1>
            {eventType.registration_welcome_video && (
                <VideoPlayer src={eventType.registration_welcome_video} />
            )}
            {eventType.registration_welcome_text && (
                <Markdown>{eventType.registration_welcome_text}</Markdown>
            )}

            <ButtonContainer>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.before", eventType)}
                >
                    Next
                </Link>
            </ButtonContainer>
        </PageContainer>
    );
};

export default WelcomeEventTypePage;
