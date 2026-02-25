<template>
    <a :href="value">{{ value }}</a>
</template>

<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms';

export default {
    mixins: [Fieldtype],
};
</script>
