import React from "react";

import { useRadius } from "@/Hooks/useRadius";

export const Radius = () => {
    const { radius, setRadius } = useRadius();
    return (
        <div className={`flex items-center space-x-6`}>
            <input
                type="number"
                value={radius}
                className="block w-full rounded-md border-0 text-black placeholder:text-gray-400 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 py-1.5 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                min={1}
                max={500}
                step={25}
                onChange={(e) => setRadius(parseInt(e.target.value))}
            />
        </div>
    );
};
