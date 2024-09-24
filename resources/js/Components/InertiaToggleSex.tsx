import { useRecoilState } from "recoil";

import { filterState } from "@/store/filtersAtom";

export const InertiaToggleSex = () => {
    const [filters, setFilters] = useRecoilState(filterState);

    const sexGroups = [
        { value: "men", label: "Men", bgClass: "bg-blue-500" },
        { value: "women", label: "Women", bgClass: "bg-pink-500" },
    ];

    const handleSexClick = (sex: string) => {
        setFilters((prev) => ({
            ...prev,
            sex: prev.sex === sex ? "" : sex,
        }));
    };

    return (
        <div className="flex items-center">
            {sexGroups.map(({ value, label, bgClass }) => (
                <button
                    key={value}
                    onClick={() => handleSexClick(value)}
                    className={`px-4 py-2 ${filters.sex === value ? `${bgClass} text-white` : "bg-gray-200"}`}
                >
                    {label}
                </button>
            ))}
        </div>
    );
};
