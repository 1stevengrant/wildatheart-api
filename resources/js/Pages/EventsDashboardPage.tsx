import React, { useEffect, useState } from "react";

import { Head } from "@inertiajs/react";
import axios from "axios";

import { Card } from "@/Components/Card";
import { HeadingOne } from "@/Components/Type/HeadingOne";
import { HeadingThree } from "@/Components/Type/HeadingThree";
import { HeadingTwo } from "@/Components/Type/HeadingTwo";
import { AdminLayout } from "@/Layouts/AdminLayout";
import { EventsDashboardStats } from "@/types/EventsDashboard";

const EventsDashboardPage = () => {
    const [loading, setLoading] = useState<boolean>(true);
    const [stats, setStats] = useState<EventsDashboardStats | null>(null);
    const [startDate, setStartDate] = useState<string>("");
    const [endDate, setEndDate] = useState<string>("");

    const fetchStats = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (startDate) params.append("start_date", startDate);
            if (endDate) params.append("end_date", endDate);

            const response = await axios.get<{ data: EventsDashboardStats }>(
                `/api/events/dashboard?${params.toString()}`
            );
            setStats(response.data);
        } catch (error) {
            console.error("Error fetching dashboard stats:", error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchStats();
    }, []);

    const handleDateFilterApply = () => {
        fetchStats();
    };

    const renderDateFilterCard = () => (
        <Card>
            <div className="p-4">
                <HeadingThree>Date Range Filter</HeadingThree>
                <div className="flex flex-wrap gap-4 mt-4">
                    <div>
                        <label
                            htmlFor="start_date"
                            className="block text-sm font-medium text-gray-700"
                        >
                            Start Date
                        </label>
                        <input
                            type="date"
                            id="start_date"
                            value={startDate}
                            onChange={(e) => setStartDate(e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div>
                        <label
                            htmlFor="end_date"
                            className="block text-sm font-medium text-gray-700"
                        >
                            End Date
                        </label>
                        <input
                            type="date"
                            id="end_date"
                            value={endDate}
                            onChange={(e) =>
                                setEndDate(e.target.value)
                            }
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                    <div className="flex items-end">
                        <button
                            type="button"
                            onClick={handleDateFilterApply}
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </Card>
    );

    const renderTotalActiveEventsCard = () => (
        <Card>
            <div className="p-4">
                <HeadingThree>
                    Total Active Events
                </HeadingThree>
                <div className="mt-4 text-4xl font-bold">
                    {stats?.total_active_events || 0}
                </div>
                <div className="text-sm text-gray-500 mt-2">
                    Any published event (public or private)
                </div>
            </div>
        </Card>
    );

    const renderEventsInTimeFrameCard = () => (
        <Card>
            <div className="p-4">
                <HeadingThree>
                    Events in Time Frame
                </HeadingThree>
                <div className="mt-4 text-4xl font-bold">
                    {stats?.events_in_time_frame || 0}
                </div>
                <div className="text-sm text-gray-500 mt-2">
                    {startDate && endDate
                        ? `From ${new Date(startDate).toLocaleDateString()} to ${new Date(endDate).toLocaleDateString()}`
                        : startDate
                          ? `From ${new Date(startDate).toLocaleDateString()}`
                          : endDate
                            ? `Until ${new Date(endDate).toLocaleDateString()}`
                            : "All time"}
                </div>
            </div>
        </Card>
    );

    const renderNewEventsCard = () => (
        <Card>
            <div className="p-4">
                <HeadingThree>
                    New Events in Time Frame
                </HeadingThree>
                <div className="mt-4 text-4xl font-bold">
                    {stats?.new_events_in_time_frame || 0}
                </div>
                <div className="text-sm text-gray-500 mt-2">
                    {startDate && endDate
                        ? `Created from ${new Date(startDate).toLocaleDateString()} to ${new Date(endDate).toLocaleDateString()}`
                        : startDate
                          ? `Created from ${new Date(startDate).toLocaleDateString()}`
                          : endDate
                            ? `Created until ${new Date(endDate).toLocaleDateString()}`
                            : "All time"}
                </div>
            </div>
        </Card>
    );

    const renderEmailStatsCard = () => (
        <Card className="md:col-span-2">
            <div className="p-4">
                <HeadingThree>
                    Email Interaction Stats
                </HeadingThree>
                <div className="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="bg-gray-50 p-4 rounded-lg">
                        <div className="text-sm font-medium text-gray-500">
                            Keep Burning Clicks
                        </div>
                        <div className="mt-2 text-2xl font-bold">
                            {stats?.email_stats.keep_burning_clicks || 0}
                        </div>
                    </div>
                    <div className="bg-gray-50 p-4 rounded-lg">
                        <div className="text-sm font-medium text-gray-500">
                            Extinguish Clicks
                        </div>
                        <div className="mt-2 text-2xl font-bold">
                            {stats?.email_stats.extinguish_clicks || 0}
                        </div>
                    </div>
                    <div className="bg-gray-50 p-4 rounded-lg">
                        <div className="text-sm font-medium text-gray-500">
                            No Action Taken
                        </div>
                        <div className="mt-2 text-2xl font-bold">
                            {stats?.email_stats.no_action_emails || 0}
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );

    return (
        <AdminLayout>
            <Head title="Events Dashboard" />

            <div className="py-8 px-4">
                <HeadingOne>Events Dashboard</HeadingOne>

                <div className="mt-6 mb-8">
                    {renderDateFilterCard()}
                </div>

                {loading ? (
                    <div className="flex justify-center items-center h-64">
                        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                    </div>
                ) : stats ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {renderTotalActiveEventsCard()}
                        {renderEventsInTimeFrameCard()}
                        {renderNewEventsCard()}
                        {renderEmailStatsCard()}
                    </div>
                ) : (
                    <div className="text-center py-12">
                        <p className="text-gray-500">No data available</p>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
};

export default EventsDashboardPage;
