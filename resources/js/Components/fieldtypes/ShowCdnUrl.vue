<template>

    <div>
        <div>
            <a :href="value">{{ value }}</a>
        </div>
    </div>

</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            //
        };
    },

    mounted() {
    }

};
</script>
