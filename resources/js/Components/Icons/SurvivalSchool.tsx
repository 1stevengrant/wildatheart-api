import React from "react";

const SurvivalSchool = ({
                            className = '',
                        }) => {
    return (
        <svg className={className} width="23" height="23" xmlns="http://www.w3.org/2000/svg">
            <circle cx="11.5" cy="11.5" r="11.5" fill="white" />
            <path d="M9,11.5c0-1.5,2-2.5,2-2.5s2,1,2,2.5-2,2.5-2,2.5-2-1-2-2.5z" fill="black"/>
            <path d="M12,11.5c0-1.5,2-2.5,2-2.5s2,1,2,2.5-2,2.5-2,2.5-2-1-2-2.5z" fill="black"/>
        </svg>
    )
}

export default SurvivalSchool
