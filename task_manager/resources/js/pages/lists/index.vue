<script setup lang="ts">
import { Head, Link, Form, router, useForm } from '@inertiajs/vue3';
import {Plus, Pencil, Trash2, ExternalLink, Loader2} from 'lucide-vue-next'; 
import {ref} from 'vue';
import InputError from '@/components/InputError.vue';
import Breadcrumb from '@/components/ui/breadcrumb/Breadcrumb.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';


 
defineOptions({
    layout: {
        breadcrumbs: [ 
            {
                title: 'Lists',
                href: '/lists',
            },
        ],
    },
});

interface TodoList{
    id: number;
    name:string;
    color?:string;
    tasks_count?: number;
    created_at: string; 
}

const props=defineProps<{
    lists: TodoList[];
}>();

const isCreateDialogOpen=ref(false);
const isEditDialogOpen=ref(false);
const editingList=ref<{
    id:number;
    name:string;
    color:string;
}| null>(null);

const deletingListId=ref<number | null>(null);

const createForm=useForm({
    name:'',
    color:'',
});

const editForm=useForm({
    name:'',
    color:'#6366f1',
});

const openEditDailog=(list)=>{
    editingList.value={
        name:list.name,
        color:list.color || '#6366f1'
    };
    editForm.name=list.name;
    editForm.color=list.color || '#6366f1';
    isCreateDialogOpen.value=true;
};


const createList=()=>{
    createForm.post('/lists',{
        preserveScroll:true,
        onSuccess:()=>{
            isCreateDialogOpen.value=false;
            createForm.reset();
        },
    });
};

const updateList=()=>{
    if(!editingList.value) return;
    
    editForm.put(`/lists/${editingList.value.id}`,{
        preserveScroll:true,
        onSuccess:()=>{
             isEditDialogOpen.value=false;
             editForm.reset();
        },
    });
};

const deleteList=(listId:number)=>{
    if(confirm('Are you sure you want to delete this list? All associated tasks will be deleted')){
        deletingListId.value=listId;
        router.delete(`/lists/${listId}`,{
            preserveScroll:true,
            onFinish:()=>{
                deletingListId.value=null;
            },
        });
    }
};


</script>

<template>
    <Head title="List" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Lists</h1>
                    <p class="text-muted-foreground">Manage your task lists</p>
                </div>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="h-4 w-4 mr-2"/>
                            Create List
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create New List</DialogTitle>
                            <DialogDescription>
                                Create a new list to organize your tasks. Give it a name and choose a color.
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="createList" class="space-y-4">
                            <div class="space-y-2">
                                <label for="name">List Name</label>
                                <Input id="name" v-model="createForm.name" required placeholder="e.g. Work Tasks"/>
                                <InputError :message="createForm.errors?.name" />
                            </div>
                            <div class="spae-y-2">
                                <label for="color">Color</label>
                                <Input id="color" v-model="createForm.color" type="color"/>
                                <InputError :message="createForm.errors?.color"/>
                            </div>
                            <Button type="submit" class="w-full" :disabled="createForm.processing">
                                <Loader2 v-if="createForm.processing" class="h-4 w-4 mr-2 animate-spin"/>
                                {{ createForm.processing?' Creating': 'Create List' }}
                            </Button>
                        </form>
                    </DialogContent>
                </Dialog>
                <Dialog v-model:open="isEditDialogOpen">
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Your List</DialogTitle>
                            <DialogDescription>
                                Update the name or color of your List!.
                            </DialogDescription>
                        </DialogHeader>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>
