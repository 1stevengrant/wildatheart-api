import { UserAvatar } from "@/Components/UserAvatar";

export const Hosts = ({
    managers,
}: {
    managers: App.Data.EventManagerData[];
}) => {
    if (!managers || managers.length === 0) {
        return null;
    }

    return (
        <div className="mt-6">
            <p className="text-sm text-n40 mb-3">Hosts</p>
            <div className="space-y-3">
                {managers.map((manager) => (
                    <div
                        key={manager.id}
                        className="flex items-center space-x-3"
                    >
                        <UserAvatar
                            image={manager.image}
                            className="w-10 h-10"
                        />
                        <p className="text-white capitalize">
                            {manager.fullname}
                        </p>
                    </div>
                ))}
            </div>
        </div>
    );
};
