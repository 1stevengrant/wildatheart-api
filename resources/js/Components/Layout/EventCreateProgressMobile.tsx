const Stepper = ({ isActive }: { isActive: boolean }) => {
    return (
        <div
            className={`h-1 border border-white w-[60px] rounded-full ${isActive ? `bg-white` : ``}`}
        />
    );
};

export const EventCreateProgressMobile = () => {
    const isEventGuideStep =
        route().current("host.eventType.index") ||
        route().current("host.eventType.before") ||
        route().current("host.eventType.faqs") ||
        route().current("host.eventType.schedule");
    const isMailingAddressStep = route().current("host.eventType.mailing");
    const isEventRegistrationStep = route().current("host.eventType.create");

    return (
        <div className={`flex items-center lg:hidden `}>
            <div className={`ml-8 text-left leading-tight`}>
                <p className={`text-white text-xs`}>
                    Step{" "}
                    {isEventGuideStep ? `1` : isMailingAddressStep ? `2` : `3`}
                </p>
                <p className={`text-white`}>Event Type</p>
                <div className={`flex items-center mt-2 space-x-2`}>
                    <Stepper isActive={isEventGuideStep} />
                    <Stepper isActive={isMailingAddressStep} />
                    <Stepper isActive={isEventRegistrationStep} />
                </div>
            </div>
        </div>
    );
};
