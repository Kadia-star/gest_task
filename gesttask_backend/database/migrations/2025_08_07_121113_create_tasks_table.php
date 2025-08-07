<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'assigned_to',
        'created_by',
    ];

    // Tâche assignée à un utilisateur (employé)
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Tâche créée par un utilisateur (manager)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}