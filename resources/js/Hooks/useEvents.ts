import { useRef, useEffect } from "react";

import axios, { CancelTokenSource } from "axios";
import { LngLatBounds } from "react-map-gl";
import { useRecoilState, useSetRecoilState } from "recoil";

import { eventsListState, eventsListLoadingState } from "@/store/eventsAtom";
import { featuredContentState } from "@/store/featuredContentAtom";

export const useEvents = () => {
    const [showLoading, setShowLoading] = useRecoilState(
        eventsListLoadingState
    );
    const setEvents = useSetRecoilState(eventsListState);
    const setFeaturedContent = useSetRecoilState(featuredContentState);
    const zoomAttemptsRef = useRef(0);
    const isZoomingRef = useRef(false);
    const pendingRequestRef = useRef(false);
    const requestIdRef = useRef(0);
    const latestBoundsRef = useRef<LngLatBounds | null>(null);
    const latestParamsRef = useRef<{
        currentSex: string;
        types: number[];
        typeGroup: string;
        startDateString: string;
        endDateString: string;
    } | null>(null);

    const MIN_EVENTS = 1;
    const MAX_ZOOM_ATTEMPTS = 5;

    // Clear zooming state and process any pending requests
    useEffect(() => {
        if (
            !isZoomingRef.current &&
            pendingRequestRef.current &&
            latestBoundsRef.current &&
            latestParamsRef.current
        ) {
            const params = latestParamsRef.current;
            pendingRequestRef.current = false;

            // Use a timeout to ensure we don't immediately re-enter a zooming state
            setTimeout(() => {
                // Create a new AbortController for this request
                const controller = new AbortController();
                getEvents(
                    latestBoundsRef.current!,
                    params.currentSex,
                    params.types,
                    params.typeGroup,
                    params.startDateString,
                    params.endDateString,
                    controller.signal
                );
            }, 100);
        }
    }, [isZoomingRef.current]);

    const getEvents = async (
        bounds: LngLatBounds,
        currentSex: string,
        types: number[],
        typeGroup: string,
        startDateString: string,
        endDateString: string,
        signal: AbortSignal
    ): Promise<{ count: number; shouldZoomOut: boolean }> => {
        // Store the latest parameters in case we need to retry after zooming
        latestBoundsRef.current = bounds;
        latestParamsRef.current = {
            currentSex,
            types,
            typeGroup,
            startDateString,
            endDateString,
        };

        // If we're currently zooming, mark the request as pending and return
        if (!bounds || isZoomingRef.current) {
            pendingRequestRef.current = true;
            return { count: 0, shouldZoomOut: false };
        }

        const ne = bounds.getNorthEast();
        const sw = bounds.getSouthWest();

        // Tag this request so only the most recent one can clear the loading
        // state. A superseded (cancelled) request must not switch off the
        // spinner for the request that replaced it.
        const requestId = ++requestIdRef.current;

        try {
            setShowLoading(true);
            const source: CancelTokenSource = axios.CancelToken.source();

            signal.addEventListener("abort", () => {
                source.cancel("Operation canceled by the user.");
            });

            const response = await axios.get(
                route("api.events.search.legacy"),
                {
                    params: {
                        sw_latitude: sw.lat,
                        sw_longitude: sw.lng,
                        ne_latitude: ne.lat,
                        ne_longitude: ne.lng,
                        sex: currentSex,
                        types: types.join(","),
                        type_group: typeGroup,
                        start_date: startDateString,
                        end_date: endDateString,
                    },
                    cancelToken: source.token,
                }
            );

            // Handle new response structure with events and featuredContent
            const responseData = response.data;
            const events = responseData.events || responseData; // Fallback for backward compatibility
            const featuredContent = responseData.featuredContent || null;

            setEvents(events);
            setFeaturedContent(featuredContent);

            const eventCount = events.length;
            console.log(
                `Found ${eventCount} events, attempt ${zoomAttemptsRef.current + 1} of ${MAX_ZOOM_ATTEMPTS}`
            );

            const shouldZoomOut =
                eventCount < MIN_EVENTS &&
                zoomAttemptsRef.current < MAX_ZOOM_ATTEMPTS;

            if (shouldZoomOut) {
                zoomAttemptsRef.current += 1;
                isZoomingRef.current = true;
                console.log("Zooming out to find more events...");
            } else {
                if (eventCount >= MIN_EVENTS) {
                    console.log(`Success: Found ${eventCount} events`);
                } else {
                    console.log(
                        `Stopping: Max attempts reached with ${eventCount} events`
                    );
                }
                zoomAttemptsRef.current = 0;
            }

            if (requestId === requestIdRef.current) {
                setShowLoading(false);
            }

            return {
                count: eventCount,
                shouldZoomOut,
            };
        } catch (error) {
            if (axios.isCancel(error)) {
                console.log("Request canceled:", error.message);
            } else {
                console.log("Error:", error);
            }
            // Only clear loading when no newer request has superseded this one.
            // If it has, the replacement controls the spinner; if it has not,
            // we must clear it here so the UI is never stuck loading.
            if (requestId === requestIdRef.current) {
                setShowLoading(false);
            }
            return {
                count: 0,
                shouldZoomOut: false,
            };
        }
    };

    const resetZoomAttempts = () => {
        zoomAttemptsRef.current = 0;
        isZoomingRef.current = false;
        pendingRequestRef.current = false;
    };

    const setZoomingComplete = () => {
        isZoomingRef.current = false;
        // Let the useEffect handle pending requests
    };

    return { getEvents, showLoading, resetZoomAttempts, setZoomingComplete };
};
