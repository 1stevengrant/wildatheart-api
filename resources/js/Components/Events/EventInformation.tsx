import React, {useEffect} from "react";
import InputLabel from "@/Components/Forms/InputLabel";
import TextInput from "@/Components/Forms/TextInput";
import InputError from "@/Components/Forms/InputError";
import {Link, useForm} from "@inertiajs/react";
import Instruction from "@/Components/Forms/Instruction";
import MapboxSearch from "@/Components/Maps/MapboxSearch";
import Map, {GeolocateControl, MapLayerMouseEvent, Marker, NavigationControl} from "react-map-gl";
import mapboxgl from "mapbox-gl";
import {useRecoilState} from "recoil";
import {selectedPlaceAtom} from "@/store";
import lookupLocation from "@/helpers/lookupLocation";
import FireMarker from "@/Components/Events/FireMarker";
import EventDisplay from "@/Components/Events/EventDisplay";
import config from "@/helpers/config";
import HeadingOne from "@/Components/Type/HeadingOne";
import LeftColumn from "@/Layouts/LeftColumn";
import RightColumn from "@/Layouts/RightColumn";
import TwoColumnWrapper from "@/Layouts/TwoColumnWrapper";
import PrimaryButton from "@/Components/Buttons/PrimaryButton";
import objectIsEmpty from "@/helpers/objectIsEmpty";
import {oneHour} from "@/helpers/timeConstants";
import FireNamingGuidelines from "@/Components/Events/FireNamingGuidelines";
import Spinner from "@/Components/Ui/Spinner";

type EventFormDataType = {
    name: string;
    state?: string | null;
    city?: string | null;
    zip?: string | null;
    country?: string | null;
    latitude: number | null;
    longitude: number | null;
    region?: string | null;
    district?: string | null;
    locality?: string | null;
    neighborhood?: string | null;
    street_address?: string | null;
    poi?: string | null;
};

const EventInformation = ({user}: any) => {
    const mapRef = React.useRef<any>(null);
    const [selectedPlace] = useRecoilState(selectedPlaceAtom)
    const [marker, setMarker] = React.useState<any>(null)
    const [userZoomLevel, setUserZoomLevel] = React.useState<number>(6)

    const handleUserLevelZoom = (zoom: number) => {
        setUserZoomLevel(zoom)
    }

    const lookupCurrentLocation = () => {
        if (navigator.geolocation) {
            const locationData = JSON.parse(localStorage.getItem('locationData') || '{}');

            if (!(objectIsEmpty(locationData) || (Date.now() - locationData.timestamp) > oneHour)) {
                flyTo([locationData.coords.longitude, locationData.coords.latitude], undefined, userZoomLevel)
                addEvent(locationData.coords.longitude, locationData.coords.latitude)
            } else {
                navigator.geolocation.getCurrentPosition(getLocationSuccess, getLocationError);
            }
        }
    }

    const getLocationSuccess = (position: GeolocationPosition) => {
        localStorage.setItem('locationData', JSON.stringify({
            timestamp: Date.now(),
            coords: {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            }
        }));
        flyTo([position.coords.longitude, position.coords.latitude], undefined, userZoomLevel)

        addEvent(position.coords.longitude, position.coords.latitude)
    }

    const getLocationError = (error: GeolocationPositionError) => {
        console.log(error)
        flyTo([0, 0], undefined, 1)
    }

    const flyTo = (
        coordinates: [number, number],
        duration: number = 2000,
        zoom: number = 6
    ) => {
        mapRef.current?.flyTo({
            essential: false,
            center: coordinates,
            duration: duration,
            zoom: zoom
        })
    }

    const [event, setEvent] = React.useState<any>({
        name: user.given_name + "'s",
        location: 'Location TBC',
        host: user.full_name,
        hostPhoto: user.image,
        country: null,
    })

    const {
        data,
        setData,
        post,
        processing,
        errors
    } = useForm<EventFormDataType>({
        name: `${user.given_name}'s`,
        state: '',
        city: '',
        zip: '',
        country: '',
        latitude: null,
        longitude: null,
        region: '',
        district: '',
        locality: '',
        neighborhood: '',
        street_address: '',
        poi: '',
    });

    useEffect(() => {
        if (selectedPlace) {
            mapRef.current?.flyTo({
                essential: false,
                center: selectedPlace.center,
                duration: 2000,
                zoom: 6
            })
            const latitude = selectedPlace.center[1],
                longitude = selectedPlace.center[0];

            addEvent(longitude, latitude)
        }
    }, [selectedPlace]);

    useEffect(() => {
        setTimeout(() => {
            lookupCurrentLocation()
        }, 50);
    }, []);

    const addEvent = async (lng: number, lat: number) => {

        setMarker({lat, lng})
        const location = await lookupLocation(lng, lat)

        let country = location.find((feature: { id: string | string[]; }) => feature.id.includes('country'))?.text,
            region = location.find((feature: { id: string | string[]; }) => feature.id.includes('region'))?.text,
            postcode = location.find((feature: { id: string | string[]; }) => feature.id.includes('postcode'))?.text,
            district = location.find((feature: { id: string | string[]; }) => feature.id.includes('district'))?.text,
            place = location.find((feature: { id: string | string[]; }) => feature.id.includes('place'))?.text,
            locality = location.find((feature: { id: string | string[]; }) => feature.id.includes('locality'))?.text,
            neighborhood = location.find((feature: {
                id: string | string[];
            }) => feature.id.includes('neighborhood'))?.text,
            streetAddress = location.find((feature: {
                id: string | string[];
            }) => feature.id.includes('address'))?.text,
            poi = location.find((feature: { id: string | string[]; }) => feature.id.includes('poi'))?.text;

        // this is janky because Mapbox might return Puerto Rico as a region instead of a country
        // but this will work for now
        if (region === 'Puerto Rico') {
            country = 'United States'
        }

        data.country = country
        data.region = region
        data.district = district
        data.locality = locality
        data.neighborhood = neighborhood
        data.street_address = streetAddress
        data.poi = poi
        data.latitude = lat
        data.longitude = lng
        data.city = place ?? locality ?? district ?? region ?? country ?? 'Middle of Nowhere'
        data.zip = postcode

        setEvent({
            ...event,
            location: place ?? locality ?? district ?? region ?? country ?? 'Middle of Nowhere',
            country: country,
            region: region
        })


    }

    const handleMapClick = (event: MapLayerMouseEvent) => {
        const {lat, lng} = event.lngLat
        addEvent(lng, lat)
    }

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(route('host.store'), {
            preserveScroll: true,
        });
    }

    return (
        <TwoColumnWrapper className={''}>
            <LeftColumn className={''}>
                <form onSubmit={handleSubmit} className={'max-w-[500px] mx-auto'}>
                    <HeadingOne className={'my-12'}>Tell us about your Fire</HeadingOne>
                    <div>
                        <InputLabel
                            htmlFor="fire_name"
                            value="Name Your Fire"
                            required/>
                        <div className="flex items-center space-x-4">
                            <TextInput
                                id="name"
                                name="name"
                                value={data.name}
                                autoComplete="off"
                                isFocused={true}
                                onChange={(e) => {
                                    setData('name', e.target.value)
                                    setEvent({...event, name: e.target.value})
                                }}
                            />
                            <div>
                                Fire
                            </div>
                        </div>
                        <Instruction>
                            This is the name that will be displayed on your Fire page.
                        </Instruction>
                        <InputError message={errors.name}/>
                    </div>
                    <FireNamingGuidelines/>
                    <EventDisplay event={event} className={'md:hidden mt-4'}/>
                    <MapboxSearch className={'mt-4'}/>
                    <div className={`w-full h-[300px] md:h-[600px] mt-4`}>
                        <Map mapLib={mapboxgl}
                             ref={mapRef}
                             dragRotate={false}
                             touchPitch={false}
                             pitchWithRotate={false}
                             maxPitch={0}
                             minPitch={0}
                             mapStyle="mapbox://styles/mapbox/navigation-day-v1"
                             initialViewState={{
                                 zoom: 6,
                             }}
                             style={{width: '100%', height: '100%', borderRadius: '0.5rem'}}
                             onClick={(event) => {
                                 handleMapClick(event)
                             }}
                             mapboxAccessToken={config('services.mapbox.token')}
                             onZoomEnd={() => handleUserLevelZoom(mapRef.current?.getZoom() || 6)}
                             attributionControl={false}>
                            <GeolocateControl position="top-left"/>
                            <NavigationControl position="top-left"/>
                            <>
                                {marker && (
                                    <Marker
                                        longitude={marker.lng}
                                        latitude={marker.lat}>
                                        <FireMarker className={'w-10 h-10'}/>
                                    </Marker>
                                )}
                            </>
                        </Map>
                    </div>

                    <div className="mt-4 space-y-4">
                        <PrimaryButton
                            disabled={event.location === 'Location TBC' && processing}
                            className={`w-full flex items-center justify-center`}>
                            {processing ? <Spinner/> : null}
                            {event.location === 'Location TBC' ? ('Pick a location to continue') : ('Next')}
                        </PrimaryButton>
                        <Link className={'text-grey-200 w-full inline-block text-center'}
                              href={route('host')}>
                            Cancel
                        </Link>
                    </div>
                </form>
            </LeftColumn>

            <RightColumn className={``}>
                <div>
                    <EventDisplay
                        className={'max-w-[350px] mx-auto hidden lg:flex'}
                        event={event}/>
                </div>
            </RightColumn>
        </TwoColumnWrapper>
    )
}

export default EventInformation;
