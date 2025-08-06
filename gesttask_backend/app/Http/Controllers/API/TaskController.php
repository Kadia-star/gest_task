<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Afficher les tâches selon le rôle
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return Task::with(['assignedTo', 'createdBy'])->get();
        }

        if ($user->role === 'manager') {
            return $user->createdTasks()->with('assignedTo')->get();
        }

        // employé
        return $user->assignedTasks()->with('createdBy')->get();
    }

    // Créer une nouvelle tâche (manager)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
        ]);

        return response()->json($task, 201);
    }

    // Mettre à jour une tâche (manager ou employé selon règle)
    public function update(Request $request, Task $task)
    {
        $user = Auth::user();

        if ($user->role === 'employe' && $task->assigned_to !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,completed',
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    // Supprimer une tâche (admin ou manager qui l’a créée)
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        // 🔐 Vérifie les droits AVANT de supprimer
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Tâche supprimée avec succès']);
    }


}
