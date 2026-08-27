<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TodoList;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    //
    public function index(): Response{
        $lists =TodoList::query()
        ->withCount(['tasks','tasks as completed_tasks_count'=> function($q){
        $q->where('completed',true);
        }])
        ->latest()
        ->get();

        $recentTasks=Task::query()
        ->with('list:id,name,color')
        ->latest()
        ->take(10)
        ->get();
        
        $totalTasks =Task::count();
        $completedTasks=Task::where('completed',true)->count();
        $pendingTasks =Task::where('completed',false)->count();

        return Inertia::render('dashboard',[
            'lists'=>$lists,
            'recentTasks'=>$recentTasks,
            'totalTasks'=>$totalTasks,
            'completedTasks'=>$completedTasks,
            'pendingTasks'=>$pendingTasks,
        ]);
    }
}
