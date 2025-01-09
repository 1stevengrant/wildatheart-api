import React from "react";

import { Menu } from "@headlessui/react";
import { ChevronDownIcon } from "@heroicons/react/16/solid";

export const OptionalInformationWrapper = ({
    children,
}: {
    children: React.ReactNode;
}) => {
    return (
        <Menu>
            <Menu.Button
                className={`mt-4 px-4 py-2 inline-flex items-center text-sm border-n400 border-2 rounded-md text-white `}
            >
                <span>Optional Information</span>
                <ChevronDownIcon
                    className={`w-4 h-4 ml-1 ui-open:rotate-180`}
                />
            </Menu.Button>
            <Menu.Items>{children}</Menu.Items>
        </Menu>
    );
};
