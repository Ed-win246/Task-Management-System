<script setup lang="ts">
import { Head,router,Link, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Plus, Pencil, Trash2, ExternalLink, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { title } from 'process';


defineOptions({
    layout:{
        breadcrumbs:[
            {
            title:'Tasks ',
            href:'/tasks',
            },
        ],
    },
});

interface Task{
    id:number;
    title:string;
    description:string;
    priority:'low'|'normal'|'high';
    completed:boolean;
    created_at:string;
    list:{
        id:number;
        name:string;
        color:string;
    }
    list_id:number;
}
interface TodoList{
    id:number;
    name:string;
    color:string;
}

interface PaginationLink{
    url:string|null;
    label:string;
    active:boolean;
}
interface PgainatedTasks{
    data:Task[];
    current_page:number;
    last_page:number;
    per_page:number;
    total:number;
    links:PaginationLink[];
}

const props=defineProps<{
    tasks:PgainatedTasks;
    lists:TodoList[];
    filters:{
        search?:string;
        priority?:string;
        list_id?:string;
    }
}>();

//filter state
const search=ref(props.filters.search || '');
const priority=ref(props.filters.priority || '');
const list_id=ref(props.filters.list_id);

//Dialog details
const isCreateDialogOpen=ref(false);
const isEditDailogOpen=ref(false);
const editingTask=ref<Task | null>(null);
const deletingTaskId=ref<number|null>(null);

const createForm=useForm({
    title:'',
    description:'',
    list_id: props.filters.list_id || '',
    priority:'normal',
});

const editForm=useForm({
    title:'',
    description:'',
    priority:'normal',
});

//watch for filters change and update with debounce
watchDebounced([search,priority,list_id],()=>{
    router.get('/tasks',{
        search:search.value || undefined,
        priority:priority.value || undefined,
    },{
        preserveState:true,
        preserveScroll:true,
    });
},
    {debounce:300
});

const toggleTaskCompletion=(task:Task)=>{
    router.put(`/tasks/${task.id}`,{
        title:task.title,
        description:task.description,
        priority:task.priority,
        completed:task.completed,
    },{
        preserveScroll:true,
    });
};
const createTask=()=>{
    createForm.post('/lists/tasks',{
        preserveScroll:true,
        onSuccess:()=>{
            isCreateDialogOpen.value=false;
            createForm.reset();
        },
    });
};
const updateTask=()=>{
    if(!editingTask.value) return;
    
    editForm.put(`/tasks/${editingTask.value}`,{
        preserveScroll:true,
        onSuccess:()=>{
            isCreateDialogOpen.value=false;
            editForm.reset();
        }
    })
};

const deleteTask=(taskId:number)=>{
    if(confirm('Are you sure  you want to delete this task?.')){
        deletingTaskId.value=taskId;
        router.delete(`/tasks/${taskId}`,{
            preserveScroll:true,
            onFinish:()=>{
                deletingTaskId.value=null;
            },
        });
    }
};

const openEditDailog =(task: Task)=>{
    editingTask.value={...task};
    editForm.title=task.title;
    editForm.description=task.description || '';
    editForm.priority=task.priority;
    isEditDailogOpen.value=true;
};

const getPriorityVariant= (priority:string): 'default'| 'secondary' | 'destructive' =>{
    switch(priority){
        case 'high' : return 'destructive';
        case 'low' : return 'secondary';
        default : return 'default';
    }
};
</script>
<template>
    <Head title="Tasks"/>
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">All Tasks</h1>
                <p class="text-muted-foreground">View and Manage all your tasks ( {{ tasks.total }} total )</p>
            </div>
            <Dialog v-model:open="isCreateDialogOpen">
                <DialogTrigger as-child>
                    <Button>
                        <Plus class="h-4 w-4 mr-2"/>
                        Add Task
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add New Task</DialogTitle>
                        <DialogDescription>Create a new Task and Assign it to a list! </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="createTask" class="space-y-4">
                        <div class="space-y-2">
                            <label for="title">Task Title</label>
                            <Input id="title" v-model="createForm.title" required placeholder="Enter task Title"/>
                            <InputError :message="createForm.errors?.title"/>
                        </div>
                        <div class="space-y-2">
                            <label for="list_id">List</label>
                            <select id="list_id" v-model="createForm.list_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm rinf-offset-backbground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2">
                                <option value="" disabled>Select a List</option>
                                <option v-for="list in lists" :key="list.id" value="list_id">
                                    {{ list.name }}
                                </option>
                            </select>
                            <InputError :message="createForm.errors?.list_id"/>
                        </div>
                        <div class="space-y-2">
                            <label for="description">Description</label>
                            <textarea id="description" v-model="createForm.description"
                            placeholder="Add a description...."
                            rows="3"
                            class="flex min-h-[80px] rounded-md w-full border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2"/>
                            <InputError :message="createForm.errors?.description"/>
                        </div>
                        <div class="space-y-2">
                            <label for="priority">Priority</label>
                            <select  id="priority" v-model="createForm.priority" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2">
                                <option value="low">Low</option>
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                            </select>
                            <InputError :message="createForm.errors?.priority"/>
                        </div>
                        <Button type="submit" class="w-full" :disabled="createForm.processing">
                            <Loader2 v-if="createForm.processing" class="h-4 w-4 mr-2 animate-spin">
                            </Loader2>
                            {{ createForm.processing? 'Creating..' :'Create Task'}}
                        </Button>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
        <Dialog v-model:open="isEditDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Task</DialogTitle>
                    <DialogDescription>Edit Task Detials.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="updateTask" class="space-y-4">
                    <div class="space-y-2">
                        <label for="title">Task Title</label>
                        <Input id="title" v-model="editForm.title" required placeholder="Edit task Title"/>
                        <InputError :message="editForm.errors?.title"/>
                    </div>
                    <div class="space-y-2">
                        <label for="description">Description</label>
                        <textarea id="description" v-model="editForm.description"
                        placeholder="Edit description...."
                        rows="3"
                        class="flex min-h-[80px] rounded-md w-full border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2"/>
                        <InputError :message="editForm.errors?.description"/>
                    </div>
                    <div class="space-y-2">
                        <label for="priority">Priority</label>
                        <select  id="priority" v-model="editForm.priority" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2">
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                        </select>
                        <InputError :message="editForm.errors?.priority"/>
                    </div>
                    <Button type="submit" class="w-full" :disabled="editForm.processing">
                        <Loader2 v-if="editForm.processing" class="h-4 w-4 mr-2 animate-spin">
                        </Loader2>
                        {{ editForm.processing? 'Updating..' :'Update Task'}}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <CardTitle>Filters</CardTitle>
                <Button variant="ghost" size="sm" @click="clearfilters">
                    <x class=" mr-2">Clear Filters</x>
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <div class="grid gap-4 md:grid-col-3">
                <div class="spae-y-2">
                    <label for="search">Search</label>
                    <div class="relative">
                        <search class="absolute left-2 top-2h-4 w-4 text-muted-foreground"/>
                        <input id="search" placeholder="search tasks..." class="pl-8">
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="list">List</label>
                    <select  id="list" v-model="list_id" class="flex w-full h-10 rounded-md w-full border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2">
                    <option value="">All Lists</option>
                    <option v-for="list in lists" :key="list.id" value="list.id">{{ list.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="priority">Priority</label>
                    <select  id="priority" v-model="list_id" class="flex w-full h-10 rounded-md w-full border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring-2">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
        </CardContent>
    </Card>
    <!--Table  -->
    
    </div>
</template>
