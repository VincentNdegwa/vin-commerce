<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import products from '@/routes/admin/products';

interface Product {
    id: number;
    name: string;
    description?: string | null;
    price: string;
    stock_quantity: number;
    status: string;
}

const props = defineProps<{ products: Product[] }>();

const destroyProduct = (productId: number) => {
    router.delete(products.destroy.url({ product: productId }), {
        preserveScroll: true,
        only: ['products'],
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '',
    },
];
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold tracking-tight">Products</h1>
            <Link :href="products.create.url()">
                <Button size="sm">New product</Button>
            </Link>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="product in props.products" :key="product.id">
                <CardHeader>
                    <CardTitle class="flex items-center justify-between gap-2">
                        <span class="truncate">{{ product.name }}</span>
                        <span class="text-sm font-medium">${{ product.price }}</span>
                    </CardTitle>
                    <CardDescription>
                        In stock: {{ product.stock_quantity }} •
                        <span class="uppercase text-xs">{{ product.status }}</span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex items-center justify-between gap-3">
                    <div class="flex gap-2">
                        <Link :href="products.edit.url({ product: product.id })">
                            <Button variant="outline" size="sm">Edit</Button>
                        </Link>
                    </div>
                    <Dialog>
                        <DialogTrigger as-child>
                            <Button
                                type="button"
                                variant="destructive"
                                size="sm"
                            >
                                Delete
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Delete product</DialogTitle>
                                <DialogDescription>
                                    Are you sure you want to delete this product? This action cannot be undone.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button type="button" variant="outline">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    @click="destroyProduct(product.id)"
                                >
                                    Confirm delete
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
