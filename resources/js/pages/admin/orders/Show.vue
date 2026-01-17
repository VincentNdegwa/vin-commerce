<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

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

import orders from '@/routes/admin/orders';
import { computed } from 'vue';

interface OrderItem {
    id: number;
    product_id?: number | null;
    product_name?: string | null;
    quantity: number;
    unit_price: string;
    subtotal: string;
}

interface OrderDetail {
    id: number;
    total_amount: string;
    status: string;
    customer?: {
        id?: number | null;
        name?: string | null;
        email?: string | null;
    } | null;
    created_at?: string | null;
    items: OrderItem[];
}

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

const props = defineProps<{ order: OrderDetail }>();

const isPending = () => props.order.status === 'pending';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: orders.index.url(),
    },
    {
        title: `Order #${props.order.id}`,
        href: '',
    },
];

const completeOrder = () => {
    router.post(orders.complete.url({ order: props.order.id }), undefined, {
        preserveScroll: true,
    });
};

const cancelOrder = () => {
    router.post(orders.cancel.url({ order: props.order.id }), undefined, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Order #${props.order.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Order #{{ props.order.id }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Status: <span :class="orderStatusClass(props.order.status)">{{ orderLabel(props.order.status) }}</span> • Total: ${{ props.order.total_amount }}
                    </p>
                    <p v-if="props.order.created_at" class="text-xs text-muted-foreground">
                        Placed: {{ new Date(props.order.created_at).toLocaleString() }}
                    </p>
                    <p v-if="props.order.customer" class="text-xs text-muted-foreground">
                        Customer: {{ props.order.customer.name }}
                        <span v-if="props.order.customer.email"> ({{ props.order.customer.email }})</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="isPending()"
                        type="button"
                        size="sm"
                        variant="default"
                        @click="completeOrder"
                    >
                        Mark complete
                    </Button>
                    <Button
                        v-if="isPending()"
                        type="button"
                        size="sm"
                        variant="destructive"
                        @click="cancelOrder"
                    >
                        Cancel order
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Items</CardTitle>
                    <CardDescription>Products included in this order.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="props.order.items.length === 0" class="text-sm text-muted-foreground">
                        No items found for this order.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="item in props.order.items"
                            :key="item.id"
                            class="flex items-center justify-between text-sm"
                        >
                            <div>
                                <div class="font-medium">
                                    {{ item.product_name ?? 'Unknown product' }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Qty: {{ item.quantity }} • Unit: ${{ item.unit_price }}
                                </div>
                            </div>
                            <div class="text-sm font-semibold">
                                ${{ item.subtotal }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
