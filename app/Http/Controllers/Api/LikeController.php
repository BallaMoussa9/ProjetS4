<?php

namespace App\Http\Controllers\Api;

use App\Models\Like;
use App\Models\Publication;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Lister tous les likes
     */
    public function all()
    {
        try {
            $likes = Like::with(['user', 'publication'])->get();

            return response()->json([
                'success' => true,
                'data' => $likes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Voir le nombre de likes d'une publication
     */
    public function index($publication_id)
    {
        try {
            $publication = Publication::find($publication_id);

            if (!$publication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Publication non trouvée'
                ], 404);
            }

            $likesCount = Like::where('publication_id', $publication_id)->count();

            return response()->json([
                'success' => true,
                'publication_id' => (int)$publication_id,
                'likes' => $likesCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter ou retirer un like (toggle)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'publication_id' => 'required|exists:publications,id'
            ]);

            $result = Like::ajouterLikes($request->publication_id, Auth::id());

            return response()->json([
                'success' => true,
                'action' => $result['action'],
                'likes' => $result['likes'],
                'message' => $result['action'] === 'liked' ? 'Like ajouté' : 'Like retiré'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un like spécifique
     */
    public function destroy($id)
    {
        try {
            $like = Like::find($id);

            if (!$like) {
                return response()->json([
                    'success' => false,
                    'message' => 'Like non trouvé'
                ], 404);
            }

            // Vérifier que l'utilisateur est le propriétaire
            if ($like->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            $like->delete();

            return response()->json([
                'success' => true,
                'message' => 'Like supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}
