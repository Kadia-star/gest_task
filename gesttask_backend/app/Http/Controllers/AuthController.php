<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    // Méthode d'inscription
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role ?? 'employe', // défaut employé
    ]);


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Méthode de connexion
   public function login(Request $request)
{
    // Valider les champs requis
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Chercher l'utilisateur par email
    $user = User::where('email', $request->email)->first();

    // Vérifier si l'utilisateur existe et si le mot de passe est correct
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
    }

    // Créer un token d'authentification avec Sanctum
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'user' => $user,
        'token' => $token
    ]);
}

    
  

public function logout(Request $request)
{
    try {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Déconnexion réussie'
            ]);
        }

        return response()->json([
            'message' => 'Token invalide ou utilisateur non connecté.'
        ], 401);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Erreur serveur',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function dashboard(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Bienvenue ' . $user->name . ' (' . $user->role . ')',
            'user' => $user
        ]);
    }



}
