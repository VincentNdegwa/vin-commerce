<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Package, ShoppingBag, ShoppingCart } from 'lucide-vue-next';
import { ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/ShopLayout.vue';
import cart from '@/routes/cart';
import { type BreadcrumbItem } from '@/types';


interface Product {
    id: number;
    name: string;
    description?: string | null;
    price: string;
    stock_quantity: number;
    image_path?: string | null;
}

const props = defineProps<{ products: { data: Product[] } }>();

const form = useForm({
    product_id: null as number | null,
    quantity: 1,
});

const quantities = ref<Record<number, number>>({});

const addToCart = (productId: number): void => {
    const quantity = quantities.value[productId] ?? 1;
    form.product_id = productId;
    form.quantity = quantity > 0 ? quantity : 1;

    form.post(cart.items.store.url(), {
        preserveScroll: true,
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shop',
        href: '',
    },
];
</script>

<template>
    <Head title="Shop" />

    <ShopLayout :breadcrumbs="breadcrumbs">
        <div class="mb-6 flex items-center justify-between">
            <h1
                class="flex items-center gap-2 text-2xl font-semibold tracking-tight"
            >
                <ShoppingBag class="h-6 w-6" />
                <span>Shop</span>
            </h1>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="product in props.products.data" :key="product.id">
                <CardHeader>
                    <CardTitle class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <Package class="h-4 w-4 text-muted-foreground" />
                            <span class="truncate">{{ product.name }}</span>
                        </div>
                        <span class="text-sm font-medium"
                            >${{ product.price }}</span
                        >
                    </CardTitle>
                    <CardDescription>
                        <span v-if="product.description">{{
                            product.description
                        }}</span>
                        <span v-else class="text-muted-foreground italic"
                            >No description</span
                        >
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <p class="text-sm text-muted-foreground">
                        In stock: {{ product.stock_quantity }}
                    </p>

                    <form
                        @submit.prevent="addToCart(product.id)"
                        class="flex items-center gap-2"
                    >
                        <Input
                            type="number"
                            name="quantity"
                            min="1"
                            :max="product.stock_quantity"
                            class="w-20"
                            v-model.number="quantities[product.id]"
                        />
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="
                                form.processing || product.stock_quantity < 1
                            "
                        >
                            <ShoppingCart class="mr-2 h-4 w-4" />
                            <span>Add to cart</span>
                        </Button>
                        <InputError :message="form.errors.quantity" />
                    </form>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
