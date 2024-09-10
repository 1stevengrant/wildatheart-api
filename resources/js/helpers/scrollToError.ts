export const scrollToError = (errors: Record<string, any>) => {
    const errorKeys = Object.keys(errors);
    console.log(errorKeys);
    if (errorKeys.length) {
        const input = document.querySelector(`[name="${errorKeys[0]}"]`);
        input?.scrollIntoView({
            behavior: "smooth",
            block: "center",
            inline: "start",
        });
        return true; // Indicate that there were errors
    }
    return false; // Indicate that there were no errors
};
