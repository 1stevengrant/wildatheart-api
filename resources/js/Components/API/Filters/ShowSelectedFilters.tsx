import React from "react";

import { useRecoilState } from "recoil";

import { typesState } from "@/store/filtersAtom";

export const ShowSelectedFilters = () => {
    const [types] = useRecoilState(typesState);
    return (
        <div className={`flex items-center space-x-1 mb-4`}>
            {types.map((type: number, index: number) => (
                <div className={`bg-white p-0.5 text-black`} key={index}>
                    {type}
                </div>
            ))}
        </div>
    );
};
