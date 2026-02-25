import Index from "./pages/Index.vue";

Statamic.booting(() => {
    Statamic.$inertia.register("command-center::Index", Index);
});
