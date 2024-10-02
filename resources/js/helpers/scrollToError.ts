export const scrollToError = () => {
    setTimeout(() => {
        const firstError = document.querySelector(".field-validation-error");
        if (firstError) {
            firstError.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
        }
    }, 0);
};
