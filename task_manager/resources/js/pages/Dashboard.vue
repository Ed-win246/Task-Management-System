<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ListTodo, CheckCircle2, Clock, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
    },
});

interface Task {
    id: number;
    title: string;
    description: string;
    priority: 'low' | 'medium' | 'high';
    completed: boolean;
    created_at: string;
    list_id: number;
}

interface TodoList {
    id: number;
    name: string;
    color?: string;
    tasks_count: number;
    completed_tasks_count: number;
    created_at: string;
}

const props = withDefaults(defineProps<{
    lists: TodoList[];
    recentTasks: Task[];
    totalTasks: number;
    completedTasks: number;
    pendingTasks: number;
}>(), {
    lists: () => [],
    recentTasks: () => [],
});

const completionRate = computed(() =>
    props.totalTasks > 0 ? Math.round((props.completedTasks / props.totalTasks) * 100) : 0
);
</script>

<template>
    <Head title="Dashboard" />
    <div class="p-6 space-y-6">
        <div>
            <h1 class="text-2xl font-bold">System Overview</h1>
            <p class="text-muted-foreground">All your lists and Tasks in one place</p>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-bold">Total Lists</CardTitle>
                    <ListTodo class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ lists.length }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-bold">Total Tasks</CardTitle>
                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ totalTasks }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Completed</CardTitle>
                    <CheckCircle2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ completedTasks }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Completion Rate</CardTitle>
                    <TrendingUp class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ completionRate }}%</div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Your Lists</CardTitle>
                    <Link href="/lists">
                        <Button variant="outline" size="sm">View</Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3" v-if="lists.length > 0">
                        <Link
                            v-for="list in lists"
                            :key="list.id"
                            :href="`/lists/${list.id}`"
                            class="block p-3 rounded-lg border hover:bg-accent transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: list.color || '#6366f1' }" />
                                    <div>
                                        <p class="font-medium">{{ list.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ list.completed_tasks_count }} / {{ list.tasks_count }} completed
                                        </p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium">{{ list.tasks_count }}</span>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        No Lists Yet
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Recent Tasks</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="recentTasks.length > 0" class="space-y-3">
                        <div v-for="task in recentTasks" :key="task.id" class="p-3 rounded-lg border">
                            <div class="flex items-start gap-4">
                                <div
                                    class="mt-1 flex-shrink-0 w-4 h-4 rounded-full flex items-center justify-center"
                                    :class="task.completed ? 'bg-green-500' : 'border-2'"
                                >
                                    <CheckCircle2 v-if="task.completed" class="w-3 h-3 text-white" />
                                </div>
                                <div class="flex flex-col gap-0.5">
                                    <p class="font-medium" :class="{ 'line-through text-muted-foreground': task.completed }">
                                        {{ task.title }}
                                    </p>
                                    <p class="text-sm text-muted-foreground capitalize">{{ task.priority }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center py-8 text-muted-foreground" v-else>
                        No Tasks Yet
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>