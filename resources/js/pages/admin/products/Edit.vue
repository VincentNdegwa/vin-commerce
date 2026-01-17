<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

import products from '@/routes/admin/products';

interface Product {
    id: number;
    name: string;
    description?: string | null;
    price: string;
    stock_quantity: number;
    status: string;
    image_path?: string | null;
}

const props = defineProps<{ product: Product; statuses: string[] }>();

const form = useForm({
    name: props.product.name,
    description: props.product.description ?? '',
    price: props.product.price,
    stock_quantity: String(props.product.stock_quantity),
    status: props.product.status.toLowerCase(),
    image_path: props.product.image_path ?? '',
});

const submit = () => {
    form.put(products.update.url({ product: props.product.id }), {
        preserveScroll: true,
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '',
    },
    {
        title: 'Edit',
        href: '',
    },
];
</script>

<template>
    <Head title="Edit product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-xl space-y-6">
            <h1 class="text-2xl font-semibold tracking-tight">Edit product</h1>

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
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        step="0.01"
                        v-model="form.price"
                    />
                    <InputError :message="form.errors.price" />
                </div>

                <div class="grid gap-2">
                    <Label for="stock_quantity">Stock quantity</Label>
                    <Input
                        id="stock_quantity"
                        name="stock_quantity"
                        type="number"
                        v-model="form.stock_quantity"
                    />
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

                <Button type="submit" :disabled="form.processing">Save changes</Button>
            </form>
        </div>
    </AppLayout>
</template>
