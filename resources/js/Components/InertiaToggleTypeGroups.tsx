import { useRecoilState } from "recoil";

import { filterState } from "@/store/filtersAtom";

export const InertiaToggleTypeGroups = () => {
    const [filters, setFilters] = useRecoilState(filterState); // Use Recoil state

    const typeGroups = [
        { value: "retreat", label: "Retreat", bgClass: "bg-blue-500" },
        { value: "experience", label: "Experience", bgClass: "bg-green-500" },
        { value: "fire", label: "Fire", bgClass: "bg-red-500" },
    ];

    const handleTypeGroupClick = (type_group: string) => {
        setFilters((prev) => ({
            ...prev,
            type_group: prev.type_group === type_group ? "" : type_group, // toggle logic
        }));
    };

    return (
        <div className="flex items-center">
            {typeGroups.map(({ value, label, bgClass }) => (
                <button
                    key={value}
                    onClick={() => handleTypeGroupClick(value)}
                    className={`px-4 py-2 ${filters.type_group === value ? `${bgClass} text-white` : "bg-gray-200"}`}
                >
                    {label}
                </button>
            ))}
        </div>
    );
};
