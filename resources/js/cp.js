import ShowCdnUrl from "./Components/fieldtypes/ShowCdnUrl.vue";
import EmailLogStatus from "./Components/fieldtypes/EmailLogStatus.vue";
import EmailHtmlPreview from "./Components/fieldtypes/EmailHtmlPreview.vue";
import EmailLogStatusIndex from "./Components/fieldtypes/EmailLogStatusIndex.vue";
import TriggerAstroBuildFieldtype from "./Components/fieldtypes/TriggerAstroBuildFieldtype.vue";

Statamic.booting(() => {
    Statamic.$components.register(
        "trigger_astro_build-fieldtype",
        TriggerAstroBuildFieldtype
    );
    Statamic.$components.register("show_cdn_url-fieldtype", ShowCdnUrl);
    Statamic.$components.register(
        "email_html_preview-fieldtype",
        EmailHtmlPreview
    );
    Statamic.$components.register(
        "email_log_status-fieldtype",
        EmailLogStatus
    );
    Statamic.$components.register(
        "email_log_status-fieldtype-index",
        EmailLogStatusIndex
    );
});
