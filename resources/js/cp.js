import ShowCdnUrl from "./Components/fieldtypes/ShowCdnUrl.vue";
import TriggerAstroBuildFieldtype from "./Components/fieldtypes/TriggerAstroBuildFieldtype.vue";

Statamic.booting(() => {
    Statamic.$components.register(
        "trigger_astro_build-fieldtype",
        TriggerAstroBuildFieldtype
    );
    Statamic.$components.register("show_cdn_url-fieldtype", ShowCdnUrl);
});
