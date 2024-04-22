import { Link, usePage } from "@inertiajs/react";
import Markdown from "react-markdown";

import { ButtonContainer } from "@/Components/Page/Event/ButtonContainer";
import { PageContainer } from "@/Components/Page/Event/PageContainer";
import { primaryButtonClasses } from "@/helpers/primaryButtonClasses";
import useRoute from "@/Hooks/useRoute";

const BeforeEventTypePage = () => {
    const page = usePage();
    const eventType = page.props.eventType as any;
    const route = useRoute();
    return (
        <PageContainer>
            <h1>Before {eventType.name}</h1>
            {eventType.registration_before_your_event_text && (
                <Markdown>
                    {eventType.registration_before_your_event_text}
                </Markdown>
            )}

            <ButtonContainer>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.index", eventType)}
                >
                    Back to Welcome
                </Link>
                <Link
                    className={primaryButtonClasses}
                    href={route("host.eventType.schedule", eventType)}
                >
                    Schedule
                </Link>
            </ButtonContainer>
        </PageContainer>
    );
};

export default BeforeEventTypePage;
