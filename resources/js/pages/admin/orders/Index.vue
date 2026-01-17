<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import ordersRoute from '@/routes/admin/orders';
import { type BreadcrumbItem } from '@/types';


interface OrderSummary {
    id: number;
    total_amount: string;
    status: string;
    customer_name?: string | null;
    created_at?: string | null;
}

const props = defineProps<{ orders: OrderSummary[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: '',
    },
];

const isPending = (order: OrderSummary) => order.status === 'pending';

const completeOrder = (order: OrderSummary) => {
    if (!isPending(order)) return;

    router.post(ordersRoute.complete.url({ order: order.id }), undefined, {
        preserveScroll: true,
    });
};

const cancelOrder = (order: OrderSummary) => {
    if (!isPending(order)) return;

    router.post(ordersRoute.cancel.url({ order: order.id }), undefined, {
        preserveScroll: true,
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

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight">Orders</h1>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="order in props.orders" :key="order.id">
                <CardHeader>
                    <CardTitle class="flex items-center justify-between gap-2">
                        <span class="truncate">Order #{{ order.id }}</span>
                        <span class="text-sm font-medium">${{ order.total_amount }}</span>
                    </CardTitle>
                    <CardDescription class="flex flex-col text-xs text-muted-foreground">
                        <span :class="orderStatusClass(order.status)">Status: {{ orderLabel(order.status) }}</span>
                        <span v-if="order.customer_name">Customer: {{ order.customer_name }}</span>
                        <span v-if="order.created_at">Placed: {{ new Date(order.created_at).toLocaleString() }}</span>
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="isPending(order)"
                            variant="outline"
                            size="sm"
                            @click="completeOrder(order)"
                        >
                            Complete
                        </Button>
                        <Button
                            v-if="isPending(order)"
                            variant="destructive"
                            size="sm"
                            @click="cancelOrder(order)"
                        >
                            Cancel
                        </Button>
                    </div>
                    <Link :href="ordersRoute.show.url({ order: order.id })">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
