// add a div with the environment name to the footer
document.addEventListener("DOMContentLoaded", function () {
    const env = import.meta.env.VITE_APP_ENV;
    if (env !== "production") {
        const div = document.createElement("div");
        div.innerHTML = "Environment: " + import.meta.env.VITE_APP_ENV;
        div.classList.add(
            "px-4",
            "py-2",
            "bg-orange",
            "text-gray-800",
            "absolute",
            "bottom-0",
            "right"
        );
        document.body.appendChild(div);
    }
});
