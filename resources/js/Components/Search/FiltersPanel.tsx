import React, { Fragment } from "react";

import { Transition } from "@headlessui/react";
import { useRecoilState } from "recoil";

import { FiltersFooter } from "@/Components/Search/FiltersFooter";
import { FiltersPanelBody } from "@/Components/Search/FiltersPanelBody";
import { FiltersPanelHeader } from "@/Components/Search/FiltersPanelHeader";
import { showFilterPanelState } from "@/store";

export const FiltersPanel = () => {
    const [showFilterPanel] = useRecoilState(showFilterPanelState);
    return (
        <Transition
            show={showFilterPanel}
            enter="transition duration-100 ease-out"
            enterFrom="transform scale-95 opacity-0"
            enterTo="transform scale-100 opacity-100"
            leave="transition duration-75 ease-out"
            leaveFrom="transform scale-100 opacity-100"
            leaveTo="transform scale-95 opacity-0"
            as={Fragment}
        >
            <div
                className={`relative top-0 right-0 left-0 bottom-0 md:flex flex-col justify-between h-full z-50 hidden`}
            >
                <FiltersPanelHeader />
                <FiltersPanelBody />
                <FiltersFooter />
            </div>
        </Transition>
    );
};
