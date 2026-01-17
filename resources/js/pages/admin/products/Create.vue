<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import products from '@/routes/admin/products';
import { type BreadcrumbItem } from '@/types';


const props = defineProps<{ statuses: string[] }>();

const form = useForm({
    name: '',
    description: '',
    price: '',
    stock_quantity: '',
    status: '',
    image_path: '',
});

if (props.statuses.length > 0) {
    form.status = props.statuses[0].toLowerCase();
}

const submit = () => {
    form.post(products.store.url(), {
        preserveScroll: true,
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '',
    },
    {
        title: 'Create',
        href: '',
    },
];
</script>

<template>
    <Head title="Create product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-xl space-y-6">
            <h1 class="text-2xl font-semibold tracking-tight">Create product</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" name="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Input id="description" name="description" v-model="form.description" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="price">Price</Label>
                    <Input id="price" name="price" type="number" step="0.01" v-model="form.price" />
                    <InputError :message="form.errors.price" />
                </div>

                <div class="grid gap-2">
                    <Label for="stock_quantity">Stock quantity</Label>
                    <Input id="stock_quantity" name="stock_quantity" type="number" v-model="form.stock_quantity" />
                    <InputError :message="form.errors.stock_quantity" />
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <select
                        id="status"
                        name="status"
                        class="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        v-model="form.status"
                    >
                        <option
                            v-for="status in props.statuses"
                            :key="status"
                            :value="status.toLowerCase()"
                        >
                            {{ status }}
                        </option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>

                <!-- <div class="grid gap-2">
                    <Label for="image_path">Image URL</Label>
                    <Input id="image_path" name="image_path" v-model="form.image_path" />
                    <InputError :message="form.errors.image_path" />
                </div> -->

                <Button type="submit" :disabled="form.processing">Save</Button>
            </form>
        </div>
    </AppLayout>
</template>
