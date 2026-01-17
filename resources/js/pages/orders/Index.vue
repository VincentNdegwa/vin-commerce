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
import ShopLayout from '@/layouts/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';

import orders from '@/routes/orders';
import { computed } from 'vue';
import shop from '@/routes/shop';

interface OrderSummary {
    id: number;
    total_amount: string;
    status: string;
    created_at?: string | null;
}

const props = defineProps<{ orders: OrderSummary[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shop',
        href: shop.index.url(),
    },
    {
        title: 'Orders',
        href: '',
    },
];

const canCancel = (order: OrderSummary): boolean => order.status === 'pending';

const cancelOrder = (orderId: number): void => {
    router.post(orders.cancel.url({ order: orderId }), undefined, {
        preserveScroll: true,
        only: ['orders'],
    });
};

const orderLabel = computed(() => {
    
    return (status: string) => {
        switch (status) {
            case 'pending':
                return 'Pending';
            case 'completed':
                return 'Completed';
            case 'cancelled':
                return 'Cancelled';
            default:
                return 'Unknown';
        }
    };
});

const orderStatusClass = computed(() => {
    return (status: string) => {
        switch (status) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800 w-fit px-2 py-1 rounded';
            case 'completed':
                return 'bg-green-100 text-green-800 w-fit px-2 py-1 rounded';
            case 'cancelled':
                return 'bg-red-100 text-red-800 w-fit px-2 py-1 rounded';
            default:
                return 'bg-gray-100 text-gray-800 w-fit px-2 py-1 rounded';
        }
    };
}); 

</script>

<template>
    <Head title="Orders" />

    <ShopLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <h1 class="text-2xl font-semibold tracking-tight">Your orders</h1>

            <div v-if="props.orders.length === 0" class="text-sm text-muted-foreground">
                You have no orders yet.
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="order in props.orders" :key="order.id">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between gap-2">
                            <span class="truncate">Order #{{ order.id }}</span>
                            <span class="text-sm font-medium">${{ order.total_amount }}</span>
                        </CardTitle>
                        <CardDescription class="flex flex-col text-xs text-muted-foreground">
                            <span>Status: <span :class="orderStatusClass(order.status)">{{ orderLabel(order.status) }}</span></span>
                            <span v-if="order.created_at">
                                Placed: {{ new Date(order.created_at).toLocaleString() }}
                            </span>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex items-center justify-between gap-3">
                        <Link :href="orders.show.url({ order: order.id })">
                            <Button variant="outline" size="sm">View</Button>
                        </Link>
                        <Button
                            v-if="canCancel(order)"
                            type="button"
                            size="sm"
                            variant="destructive"
                            @click="cancelOrder(order.id)"
                        >
                            Cancel
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </ShopLayout>
</template>
