<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
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
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

withDefaults(
	defineProps<{
		breadcrumbs?: BreadcrumbItemType[];
	}>(),
	{
		breadcrumbs: () => [],
	},
);

const page = usePage();

const notifications = computed(() => {
	return (page.props as any)?.notifications ?? null;
});

const unreadNotificationCount = computed(() => {
	return notifications.value?.unread_count ?? 0;
});

const notificationItems = computed(() => {
	return notifications.value?.items ?? [];
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
	<header
		class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
	>
		<div class="flex w-full items-center justify-between gap-2">
			<div class="flex items-center gap-2">
				<SidebarTrigger class="-ml-1" />
				<template v-if="breadcrumbs && breadcrumbs.length > 0">
					<Breadcrumbs :breadcrumbs="breadcrumbs" />
				</template>
			</div>
			<div class="flex items-center">
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
					<DropdownMenuContent align="end" class="w-80">
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
			</div>
		</div>
	</header>
</template>
