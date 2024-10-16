import { usePage } from "@inertiajs/react";

import { HeadingOne } from "@/Components/Type/HeadingOne";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

type EventStats = {
    fires: number;
    wahRetreats: number;
    captivatingRetreats: number;
    experiences: number;
};
type PageProps = {
    eventStats: EventStats;
    date: string;
};

const Stat = ({ label, value }: { label: string; value: number }) => {
    return (
        <li className="flex space-x-2 items-center">
            <span className="font-semibold text-gray-700">{label}:</span>
            <span className="text-orange">{value}</span>
        </li>
    );
};
const ReportingPage = () => {
    const { eventStats, date } = usePage<PageProps>().props;
    console.log(date);
    return (
        <EmptyLayout title={`Reporting`}>
            <div className={`p-4`}>
                <HeadingOne>Reporting since {date}</HeadingOne>
                <div className="grid grid-cols-3 gap-6">
                    {eventStats ? (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
                            <div className="p-6 bg-white border-b border-gray-200">
                                <h2 className="text-2xl font-bold mb-4 text-gray-800">
                                    Event Stats
                                </h2>
                                <ul className="space-y-3">
                                    <Stat
                                        label={"Fires"}
                                        value={eventStats.fires}
                                    />
                                    <Stat
                                        label={"WAH Retreats"}
                                        value={eventStats.wahRetreats}
                                    />
                                    <Stat
                                        label={"Captivating Retreats"}
                                        value={eventStats.captivatingRetreats}
                                    />
                                    <Stat
                                        label={"Experiences"}
                                        value={eventStats.experiences}
                                    />
                                </ul>
                            </div>
                        </div>
                    ) : (
                        <div className="text-gray-500">Loading stats...</div>
                    )}
                </div>
            </div>
        </EmptyLayout>
    );
};

export default ReportingPage;
