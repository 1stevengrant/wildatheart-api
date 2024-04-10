import React from "react";

export const setStates = (
    stateSetters: Array<React.Dispatch<React.SetStateAction<any>>>,
    value: any
) => {
    stateSetters.forEach((setter) => setter(value));
};
