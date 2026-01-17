<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';

interface FlashProps {
    success?: string | null;
    error?: string | null;
    info?: string | null;
}

const page = usePage();

const flash = computed<FlashProps | undefined>(() => {
    const props = page.props as any;

    return props.flash as FlashProps | undefined;
});

watch(
    flash,
    (value) => {
        if (!value) {
            return;
        }

        if (value.success) {
            toast.success(value.success);
        }

        if (value.error) {
            toast.error(value.error);
        }

        if (value.info) {
            toast.info(value.info);
        }
    },
    { deep: true },
);
</script>

<template>
    <div />
</template>
