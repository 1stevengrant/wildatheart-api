import React, { useState } from "react";

import { router } from "@inertiajs/react";
import { DatePickerInput } from "@mantine/dates";

import { fromCalendarDate, toCalendarDate } from "@/helpers/calendarDate";
import useTypedPage from "@/Hooks/useTypedPage";
import { EmptyLayout } from "@/Layouts/EmptyLayout";

type EventStats = {
    fires: EventTypeObject;
    wahRetreats: EventTypeObject;
    captivatingRetreats: EventTypeObject;
    experiences: EventTypeObject;
    captivatingFires: EventTypeObject;
    survivalSchools: EventTypeObject;
};

type EventTypeObject = {
    count: number;
    hosts: number;
};

type Props = {
    eventStats: EventStats;
    date: string;
    year: number;
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
                <span className="font-semibold text-n400">{label}:</span>
                <span className="text-p200">{value.count}</span>
                <div className={`text-n90 text-sm`}>Hosts: {value.hosts}</div>
            </div>

            {statInfo && <div className={`text-n90 text-sm`}>{statInfo}</div>}
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
                <h2 className="text-2xl font-bold mb-4 text-n700">{title}</h2>
                {byline && <p className="text-n500 italic">{byline}</p>}
                <hr className="my-4" />
                <ul className="space-y-3">{children}</ul>
            </div>
        </div>
    );
};

const ReportingPage = () => {
    const { eventStats, date, yearStats, year } = useTypedPage<Props>().props;
    const [reportDate, setReportDate] = useState(fromCalendarDate(date));

    const handleReportDateChange = (value: Date | null) => {
        if (value) {
            setReportDate(value);
            router.get(
                "/internal/reporting",
                { date: toCalendarDate(value) },
                { preserveState: true, preserveScroll: true }
            );
        }
    };

    return (
        <EmptyLayout title={`Reporting`}>
            <div className={`p-4 reporting`}>
                <div className="grid grid-cols-3 gap-6">
                    <div className="bg-white p-4 rounded-lg">
                        <DatePickerInput
                            className={`[&>.mantine-DatePickerInput-label]:text-black`}
                            label="Report date"
                            placeholder="Select report date"
                            value={reportDate}
                            clearable
                            onChange={(value) => handleReportDateChange(value)}
                        />
                    </div>
                </div>
                <div className="grid grid-cols-3 gap-6">
                    {eventStats && (
                        <StatCard title={`Events Created After ${date}`}>
                            <Stat label={"Fires"} value={eventStats.fires} />
                            <Stat
                                label={"Captivating Fires"}
                                value={eventStats.captivatingFires}
                            />
                            <Stat
                                label={"WAH Retreats"}
                                value={eventStats.wahRetreats}
                            />
                            <Stat
                                label={"Survival Schools"}
                                value={eventStats.survivalSchools}
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
                                label={"Captivating Fires"}
                                value={yearStats.captivatingFires}
                                statInfo={`(may have been created before ${year} but either stopped in ${year} or continued to burn)`}
                            />
                            <Stat
                                label={"WAH Retreats"}
                                value={yearStats.wahRetreats}
                            />
                            <Stat
                                label={"Survival Schools"}
                                value={yearStats.survivalSchools}
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
