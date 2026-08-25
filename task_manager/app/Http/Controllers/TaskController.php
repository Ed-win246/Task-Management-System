<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\TodoList;
use Inertia\Inertia;
use Inertia\Response;
use App\MOdels\Task;

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

    public function store(Request $request): RedirectResponse{
        $validated=$request->validate([
            'title'=>'required|string|max:255',
            'description'=>'required|string',
            'priority'=>'nullable|string|max:255',
            'completed'=>'nullable|boolean',
            'list_id'=>'required|exists:lists,id',
        ]);
        $validated['completed']=(bool)($validated['completed']??false);
        $validated['priority']=$validated['priority']??'normal';

        Task::create($validated);
        return redirect()->back();
    }

    public function update(Request $request , Task $task ): RedirectResponse{
        $validated=$request->validate([
            'title'=>'required|string|max:255',
            'description'=>'required|string',
            'priority'=>'nullable|string|max:255',
            'completed'=>'nullable|string|max:255',
        ]);
        $validated['completed']=(bool)($validated['completed']??$task->completed);
        $validated['priority']=$validated['priority']??$task->priority;

        $task->update($validated);
        return redirect()->back();
    }

    public function destroy(Task $task): RedirectResponse{
        $task->delete();
        return redirect()->back();
    }
}
