import AstroBuild from "./components/AstroBuild.vue";

Statamic.booting(() => {
    Statamic.$components.register("astro-build", AstroBuild);
});
