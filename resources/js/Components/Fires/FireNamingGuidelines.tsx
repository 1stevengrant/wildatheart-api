import React from "react";

interface FireNamingGuidelinesProps {
    className?: string;
}

const FireNamingGuidelines = ({className}: FireNamingGuidelinesProps) => {
    return (
        <div className={`${className} text-sm text-grey-200 mt-4`}>
            <p>Fire Naming Guidelines</p>
            <ul className="mt-2 list-disc pl-8">
                <li>No special characters/emojis</li>
                <li>
                    Avoid location specific names as we give location
                    elsewhere on your page. Example "Dallas Fire"
                </li>
                <li>Putting "Fire" in the name is not needed</li>
            </ul>
        </div>
    )
}

export default FireNamingGuidelines;
