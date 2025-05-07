import { ConnectWithHost } from "@/Components/Events/Detail/ConnectWithHost";
import { Host } from "@/Components/Events/Detail/Host";

export const SidebarContent = ({ event }: { event: App.Data.EventData }) => {
    return (
        <div className="relative hidden md:block md:w-1/3">
            <Host host={event.host} host_photo={event.host_photo} />

            <ConnectWithHost className="mt-6 sticky top-4" />
        </div>
    );
};
