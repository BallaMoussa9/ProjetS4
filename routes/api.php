<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicationController;
use App\Http\Controllers\Api\CommentaireController;
use App\Http\Controllers\Api\LikeController;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // ========== PUBLICATIONS - CRUD COMPLET ==========
    Route::get('/publications', [PublicationController::class, 'index']);           // Lister
    Route::post('/publications', [PublicationController::class, 'store']);          // Créer
    Route::get('/publications/{id}', [PublicationController::class, 'show']);       // Voir une (optionnel)
    Route::put('/publications/{id}', [PublicationController::class, 'update']);     // Modifier
    Route::delete('/publications/{id}', [PublicationController::class, 'destroy']); // Supprimer

    // ========== COMMENTAIRES - CRUD COMPLET ==========
    Route::get('/commentaires', [CommentaireController::class, 'all']);              // Lister tous
    Route::get('/commentaires/{publication_id}', [CommentaireController::class, 'index']); // Par publication
    Route::post('/commentaires', [CommentaireController::class, 'store']);           // Créer
    Route::put('/commentaires/{id}', [CommentaireController::class, 'update']);      // Modifier
    Route::delete('/commentaires/{id}', [CommentaireController::class, 'destroy']);  // Supprimer

    // ========== LIKES - CRUD COMPLET ==========
    Route::get('/likes', [LikeController::class, 'all']);                            // Lister tous
    Route::get('/likes/{publication_id}', [LikeController::class, 'index']);         // Par publication
    Route::post('/likes', [LikeController::class, 'store']);                         // Ajouter/retirer (toggle)
    Route::delete('/likes/{id}', [LikeController::class, 'destroy']);                // Supprimer spécifique
});
