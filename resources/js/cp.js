import ShowCdnUrl from "./Components/fieldtypes/ShowCdnUrl.vue";
import TriggerAstroBuildFieldtype from "./Components/fieldtypes/TriggerAstroBuildFieldtype.vue";

Statamic.booting(() => {
    Statamic.$components.register(
        "trigger_astro_build-fieldtype",
        TriggerAstroBuildFieldtype
    );
    Statamic.$components.register("show_cdn_url-fieldtype", ShowCdnUrl);
});

// add a div with the environment name to the footer
document.addEventListener("DOMContentLoaded", function () {
    const env = import.meta.env.VITE_APP_ENV;
    if (env !== "production") {
        const div = document.createElement("div");
        div.innerHTML = "Environment: " + import.meta.env.VITE_APP_ENV;
        div.classList.add(
            "px-4",
            "py-2",
            "bg-p200",
            "text-n700",
            "absolute",
            "bottom-0",
            "right"
        );
        document.body.appendChild(div);
    }
});
