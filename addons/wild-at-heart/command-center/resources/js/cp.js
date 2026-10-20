import Ask from "./pages/Ask.vue";
import Index from "./pages/Index.vue";
import AskPlatformWidget from "./components/AskPlatformWidget.vue";

Statamic.booting(() => {
    Statamic.$inertia.register("command-center::Index", Index);
    Statamic.$inertia.register("command-center::Ask", Ask);
    Statamic.$components.register("ask-platform-widget", AskPlatformWidget);
});
