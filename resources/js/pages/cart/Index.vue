<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';
import { CreditCard, RefreshCw, ShoppingCart, Trash2 } from 'lucide-vue-next';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/ShopLayout.vue';
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
import cart from '@/routes/cart';
import checkout from '@/routes/checkout';
import shop from '@/routes/shop';

interface CartItemProduct {
	id: number;
	name: string;
	price: string;
	stock_quantity: number;
	image_path?: string | null;
}

interface CartItem {
	id: number;
	quantity: number;
	unit_price: string;
	subtotal: string;
	product: CartItemProduct | null;
}

const props = defineProps<{ cart: { items: CartItem[]; total: string } }>();

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Shop',
		href: shop.index.url(),
	},
	{
		title: 'Cart',
		href: '',
	},
];

const quantities = ref<Record<number, number>>({});

watchEffect(() => {
	const map: Record<number, number> = {};
	for (const item of props.cart.items) {
		map[item.id] = item.quantity;
	}
	quantities.value = map;
});

const updateForm = useForm({
	quantity: 1,
});

const checkoutForm = useForm({});

const updateItem = (itemId: number): void => {
	const quantity = quantities.value[itemId] ?? 0;
	updateForm.quantity = quantity;

	updateForm.put(cart.items.update.url({ item: itemId }), {
		preserveScroll: true,
		only: ['cart', 'flash'],
	});
};

const removeItem = (itemId: number): void => {
	router.delete(cart.items.destroy.url({ item: itemId }), {
		preserveScroll: true,
		only: ['cart', 'flash'],
	});
};

const checkoutCart = (): void => {
	checkoutForm.post(checkout.store.url(), {
		preserveScroll: true,
		only: ['cart', 'flash'],
	});
};
</script>

<template>
	<Head title="Cart" />

	<ShopLayout :breadcrumbs="breadcrumbs">
		<div class="space-y-6">
			<h1 class="flex items-center gap-2 text-2xl font-semibold tracking-tight">
				<ShoppingCart class="h-6 w-6" />
				<span>Your cart</span>
			</h1>

			<div v-if="props.cart.items.length === 0" class="text-sm text-muted-foreground">
				Your cart is empty.
			</div>

			<div v-else class="space-y-4">
				<div
					v-for="item in props.cart.items"
					:key="item.id"
					class="flex items-center justify-between gap-4 rounded-lg border p-4"
				>
					<div class="space-y-1">
						<div class="font-medium">
							{{ item.product?.name ?? 'Unknown product' }}
						</div>
						<div class="text-xs text-muted-foreground">
							Unit: ${{ item.unit_price }} • Subtotal: ${{ item.subtotal }}
						</div>
					</div>

					<div class="flex items-center gap-3">
						<form
							@submit.prevent="updateItem(item.id)"
							class="flex items-center gap-2"
						>
							<Input
								type="number"
								name="quantity"
								class="w-20"
								min="0"
								v-model.number="quantities[item.id]"
							/>
							<Button
								type="submit"
								size="icon"
								variant="outline"
								:disabled="updateForm.processing"
							>
								<RefreshCw class="h-4 w-4" />
								<span class="sr-only">Update quantity</span>
							</Button>
							<InputError :message="updateForm.errors.quantity" />
						</form>

						<Dialog>
							<DialogTrigger as-child>
								<Button type="button" variant="ghost" size="icon">
									<Trash2 class="h-4 w-4" />
									<span class="sr-only">Remove item</span>
								</Button>
							</DialogTrigger>
							<DialogContent>
								<DialogHeader>
									<DialogTitle>Remove item</DialogTitle>
									<DialogDescription>
										Are you sure you want to remove this item from your cart?
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
										@click="removeItem(item.id)"
									>
										Confirm remove
									</Button>
								</DialogFooter>
							</DialogContent>
						</Dialog>
					</div>
				</div>

				<div class="flex items-center justify-between border-t pt-4">
					<div class="text-sm text-muted-foreground">Total</div>
					<div class="text-lg font-semibold">${{ props.cart.total }}</div>
				</div>

				<form @submit.prevent="checkoutCart">
					<Button
						type="submit"
						:disabled="props.cart.items.length === 0 || checkoutForm.processing"
					>
						<CreditCard class="mr-2 h-4 w-4" />
						<span>Checkout</span>
					</Button>
				</form>
			</div>
		</div>
	</ShopLayout>
</template>
