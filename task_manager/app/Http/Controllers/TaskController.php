<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\TodoList;
use Inertia\Inertia;
use Inertia\Response;
use App\MOdels\Task;
use Laravel\Mcp\Server\Annotations\Priority;

class TaskController extends Controller
{
    //
    public function index(Request $request): Response{
        $query=Task::query()->with('list.id,name,color');

        if($request->filled('search')){
            $query->where(function ($q) use ($request){
                $q->where('title','like','%' . $request->search.'%')
                ->where('description','like','%' .$request->search.'%');
            });
        }

        if($request->filled('priority')){
            $query->where('priority',$request->priority);
        }

        if($request->filled('list.id')){
            $query->where('list.id', $request->list_id);
        }

        $tasks = $query->latest()->paginate(10)->withQueryString();
        $lists = TodoList::select(['id','name','color'])->get();

        return Inertia::render('tasks/index',[
            'tasks'=>$tasks,
            'lists'=>$lists,
            'filters'=>$request->only(['search','priority','list_id']),
        ]);
    }
}
