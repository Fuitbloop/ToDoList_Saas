<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubtaskController extends Controller
{
  public function index()
  {
    $id = request()->query('task_id');
    if (!$id) return response()->json(['message' => 'Task ID is required'], 400);

    $user = auth()->user();
    $task = $user->tasks()->findOrFail($id);
    if (auth()->id() !== $task->user_id) return response()->json(['message' => 'Unauthorized'], 403);

    return response()->json($task->subtasks);
  }

  public function store(Request $request)
  {
    $id = $request->query('task_id');
    if (!$id) return response()->json(['message' => 'Task ID is required'], 400);

    $user = auth()->user();
    $task = $user->tasks()->findOrFail($id);
    if (auth()->id() !== $task->user_id) return response()->json(['message' => 'Unauthorized'], 403);

    $validator = Validator::make($request->all(), [
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
    ]);
    if ($validator->fails()) return response()->json($validator->errors(), 422);

    $subtask = $task->subtasks()->create($request->only('title', 'description'));
    return response()->json($subtask, 201);
  }

  public function update(Request $request, string $id)
  {
    $subtask = Subtask::findOrFail($id);
    $task = $subtask->task;
    if (auth()->id() !== $task->user_id) return response()->json(['message' => 'Unauthorized'], 403);

    $validator = Validator::make($request->all(), [
      'title' => 'sometimes|required|string|max:255',
      'description' => 'sometimes|nullable|string',
    ]);
    if ($validator->fails()) return response()->json($validator->errors(), 422);

    $subtask->update($request->only(['title', 'description']));
    return response()->json($subtask);
  }

  public function changeStatus(Request $request)
{
    Log::info('changeStatus payload', $request->all());

    $id = $request->input('subtask_id');
    $subtask = Subtask::findOrFail($id);
    $task = $subtask->task;

    if (auth()->id() !== $task->user_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validator = Validator::make($request->all(), [
        'status' => 'required|in:pending,in_progress,completed',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $subtask->status = $request->status;
    $subtask->save();

    return response()->json($subtask);
}


  public function destroy(string $id)
  {
    $subtask = Subtask::findOrFail($id);
    $task = $subtask->task;
    if (auth()->id() !== $task->user_id || $subtask->task_id !== $task->id)
      return response()->json(['message' => 'Unauthorized'], 403);

    $subtask->delete();
    return response()->json(['message' => 'Subtask deleted successfully']);
  }
}
