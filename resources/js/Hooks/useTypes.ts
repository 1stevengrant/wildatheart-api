import { useRecoilState } from "recoil";

import { EVENT_FOR_MEN, EVENT_FOR_WOMEN } from "@/helpers/eventSex";
import {
    experienceTypeGroup,
    retreatTypeGroup,
} from "@/helpers/eventTypeGroups";
import useTypedPage from "@/Hooks/useTypedPage";
import { typesState } from "@/store/filtersAtom";

interface Props {
    [key: string]: any;
}

export const useTypes = () => {
    const [types, setTypes] = useRecoilState(typesState);

    const handleTypesChange = (eventTypeId: number) => {
        if (types.includes(eventTypeId)) {
            setTypes(types.filter((id: number) => id !== eventTypeId));
        } else {
            setTypes([...types, eventTypeId]);
        }
    };
    const resetTypes = () => setTypes([]);

    const setTypesTo = (eventTypeId: number[]) => setTypes(eventTypeId);

    const props = useTypedPage<Props>().props;
    const experienceTypes = props.experienceTypes;
    const retreatTypes = props.retreatTypes;
    const fireTypes = props.fireTypes;

    const experienceTypeIds = experienceTypes.map((type: any) => type.id);
    const retreatTypeIds = retreatTypes.map((type: any) => type.id);
    const fireTypeIds = fireTypes.map((type: any) => type.id);

    const experienceEventTypes = props.allEventTypes.filter(
        (eventType: any) => eventType.group_type === experienceTypeGroup
    );

    const retreatEventTypes = props.allEventTypes
        .filter((eventType: any) => eventType.group_type === retreatTypeGroup)
        .sort((a: any, b: any) => (a.order ?? 0) - (b.order ?? 0));

    const mensEventTypeIds = props.allEventTypes
        .filter((eventType: any) => {
            return eventType.sex === EVENT_FOR_MEN;
        })
        .map((eventType: any) => {
            return eventType.id;
        });

    const womensEventTypeIds = props.allEventTypes
        .filter((eventType: any) => {
            return eventType.sex === EVENT_FOR_WOMEN;
        })
        .map((eventType: any) => {
            return eventType.id;
        });

    return {
        types,
        handleTypesChange,
        resetTypes,
        setTypesTo,
        experienceTypeIds,
        retreatTypeIds,
        fireTypeIds,
        experienceEventTypes,
        retreatEventTypes,
        womensEventTypeIds,
        mensEventTypeIds,
    };
};
