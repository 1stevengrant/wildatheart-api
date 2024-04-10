export const localStorageLocation = () => {
    return !!(
        localStorage.getItem("latitude") && localStorage.getItem("longitude")
    );
};
