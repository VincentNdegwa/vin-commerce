<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, Receipt, ShoppingBag, ShoppingCart } from 'lucide-vue-next';
import { computed } from 'vue';

import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Button } from '@/components/ui/button';
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuLabel,
	DropdownMenuSeparator,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import cart from '@/routes/cart';
import orders from '@/routes/orders';
import shop from '@/routes/shop';
import type { BreadcrumbItemType } from '@/types';

interface Props {
	breadcrumbs?: BreadcrumbItemType[];
}

const { breadcrumbs } = withDefaults(defineProps<Props>(), {
	breadcrumbs: () => [],
});

const page = usePage();

const user = computed(() => {
	return (page.props as any)?.auth?.user ?? null;
});

const notifications = computed(() => {
	return (page.props as any)?.notifications ?? null;
});

const unreadNotificationCount = computed(() => {
	return notifications.value?.unread_count ?? 0;
});

const notificationItems = computed(() => {
	return notifications.value?.items ?? [];
});

const cartItemCount = computed(() => {
	const props = page.props as any;
	return props?.cart?.item_count ?? 0;
});

const goToNotification = (notification: any) => {
	const url = notification.data?.action_url as string | undefined;
	if (!url) return;

	router.visit(url);
};
const markAllNotificationsRead = () => {
	router.post('/notifications/mark-all-read', undefined, {
		preserveScroll: true,
	});
};
</script>

<template>
	<div class="flex min-h-screen flex-col bg-background">
		<header class="border-b bg-card/80 backdrop-blur">
			<div
				class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 md:py-4"
			>
				<div class="flex items-center gap-3 md:gap-4">
					<Link
						:href="shop.index.url()"
						class="flex items-center gap-2 text-lg font-semibold tracking-tight text-foreground hover:text-primary transition-colors"
					>
						<ShoppingBag class="h-5 w-5 md:h-6 md:w-6" />
						<span class="hidden sm:inline">Shop</span>
					</Link>
					<div class="hidden md:block">
						<template v-if="breadcrumbs && breadcrumbs.length > 0">
							<Breadcrumbs :breadcrumbs="breadcrumbs" />
						</template>
					</div>
				</div>
				<div class="flex items-center gap-2 md:gap-3">
					<template v-if="user">
						<Link :href="orders.index.url()">
							<Button variant="ghost" size="icon" class="relative h-9 w-9">
								<Receipt class="h-4 w-4" />
								<span class="sr-only">Orders</span>
							</Button>
						</Link>
						<Link :href="cart.index.url()">
							<Button variant="ghost" size="icon" class="relative h-9 w-9">
								<ShoppingCart class="h-4 w-4" />
								<span class="sr-only">Cart</span>
								<span
									v-if="cartItemCount > 0"
									class="absolute -top-1 -right-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
								>
									{{ cartItemCount }}
								</span>
							</Button>
						</Link>
						<DropdownMenu>
							<DropdownMenuTrigger as-child>
								<Button
									variant="ghost"
									size="icon"
									class="relative h-9 w-9"
								>
									<Bell class="h-4 w-4" />
									<span class="sr-only">Notifications</span>
									<span
										v-if="unreadNotificationCount > 0"
										class="absolute -top-1 -right-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
									>
										{{ unreadNotificationCount }}
									</span>
								</Button>
							</DropdownMenuTrigger>
							<DropdownMenuContent align="end" class="w-72">
								<DropdownMenuLabel>Notifications</DropdownMenuLabel>
								<DropdownMenuSeparator />
								<div
									v-if="!notificationItems.length"
									class="px-2 py-1.5 text-xs text-muted-foreground"
								>
									No notifications
								</div>
								<template v-else>
									<DropdownMenuItem
										v-for="notification in notificationItems"
										:key="notification.id"
										class="flex flex-col items-start gap-0.5 text-sm cursor-pointer"
										@click="goToNotification(notification)"
									>
										<span class="font-medium">
											{{ notification.data.title ?? 'Notification' }}
										</span>
										<span v-if="notification.data.message" class="text-xs text-muted-foreground">
											{{ notification.data.message }}
										</span>
									</DropdownMenuItem>
									<DropdownMenuSeparator />
									<DropdownMenuItem
										class="justify-center text-xs text-muted-foreground cursor-pointer"
										@click.stop="markAllNotificationsRead"
									>
										Mark all as read
									</DropdownMenuItem>
								</template>
							</DropdownMenuContent>
						</DropdownMenu>
						<DropdownMenu>
							<DropdownMenuTrigger as-child>
								<Button
									variant="outline"
									size="sm"
									class="hidden sm:inline-flex gap-2"
								>
									<span class="truncate max-w-[8rem] text-xs md:text-sm">{{ user.name }}</span>
								</Button>
							</DropdownMenuTrigger>
							<DropdownMenuContent align="end">
								<UserMenuContent :user="user" />
							</DropdownMenuContent>
						</DropdownMenu>
					</template>
				</div>
			</div>
		</header>
		<main class="mx-auto flex w-full max-w-6xl flex-1 flex-col px-4 py-6">
			<slot />
		</main>
	</div>
</template>
