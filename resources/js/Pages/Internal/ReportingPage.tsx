import React from "react";

import { usePage } from "@inertiajs/react";

import { EmptyLayout } from "@/Layouts/EmptyLayout";

type EventStats = {
    fires: EventTypeObject;
    wahRetreats: EventTypeObject;
    captivatingRetreats: EventTypeObject;
    experiences: EventTypeObject;
};
type EventTypeObject = {
    count: number;
    hosts: number;
};

type PageProps = {
    eventStats: EventStats;
    date: string;
    yearStats: EventStats;
};

const Stat = ({
    label,
    value,
    statInfo,
}: {
    label: string;
    value: EventTypeObject;
    statInfo?: string;
}) => {
    return (
        <li>
            <div className={`flex space-x-2 items-center`}>
                <span className="font-semibold text-gray-700">{label}:</span>
                <span className="text-orange">{value.count}</span>
                <div className={`text-grey text-sm`}>Hosts: {value.hosts}</div>
            </div>

            {statInfo && <div className={`text-grey text-sm`}>{statInfo}</div>}
        </li>
    );
};

const StatCard = ({
    children,
    title,
    byline,
}: {
    children: React.ReactNode;
    title: string;
    byline?: string;
}) => {
    return (
        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4">
            <div className="p-6 bg-white">
                <h2 className="text-2xl font-bold mb-4 text-gray-800">
                    {title}
                </h2>
                {byline && <p className="text-grey italic">{byline}</p>}
                <hr className="my-4" />
                <ul className="space-y-3">{children}</ul>
            </div>
        </div>
    );
};
const ReportingPage = () => {
    const { eventStats, date, yearStats, year } = usePage<PageProps>().props;
    return (
        <EmptyLayout title={`Reporting`}>
            <div className={`p-4`}>
                <div className="grid grid-cols-3 gap-6">
                    {eventStats && (
                        <StatCard title={`Events Created After ${date}`}>
                            <Stat label={"Fires"} value={eventStats.fires} />
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
                        </StatCard>
                    )}
                    {yearStats && (
                        <StatCard
                            title={`Events Held in ${year}`}
                            byline={`This includes events that have been deleted by hosts or admins`}
                        >
                            <Stat
                                label={"Fires"}
                                value={yearStats.fires}
                                statInfo={`(may have been created before ${year} but either stopped in ${year} or continued to burn)`}
                            />
                            <Stat
                                label={"WAH Retreats"}
                                value={yearStats.wahRetreats}
                            />
                            <Stat
                                label={"Captivating Retreats"}
                                value={yearStats.captivatingRetreats}
                            />
                            <Stat
                                label={"Experiences"}
                                value={yearStats.experiences}
                            />
                        </StatCard>
                    )}
                </div>
            </div>
        </EmptyLayout>
    );
};

export default ReportingPage;
