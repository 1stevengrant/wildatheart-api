import { useRecoilState } from "recoil";

import { filterState } from "@/store/filtersAtom";

export const InertiaToggleGroupType = () => {
    const [filters, setFilters] = useRecoilState(filterState); // Use Recoil state

    const groupTypes = [
        { value: "retreat", label: "Retreat", bgClass: "bg-blue-500" },
        { value: "experience", label: "Experience", bgClass: "bg-green-500" },
        { value: "fire", label: "Fire", bgClass: "bg-red-500" },
    ];

    const handleTypeGroupClick = (group_type: string) => {
        setFilters((prev) => ({
            ...prev,
            group_type: prev.group_type === group_type ? "" : group_type, // toggle logic
        }));
    };

    return (
        <div className="flex items-center">
            {groupTypes.map(({ value, label, bgClass }) => (
                <button
                    key={value}
                    onClick={() => handleTypeGroupClick(value)}
                    className={`px-4 py-2 ${filters.group_type === value ? `${bgClass} text-white` : "bg-gray-200"}`}
                >
                    {label}
                </button>
            ))}
        </div>
    );
};
