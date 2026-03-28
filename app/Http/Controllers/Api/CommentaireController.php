<?php

namespace App\Http\Controllers\Api;

use App\Models\Commentaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentaireController extends Controller
{
    public function index($publication_id)
    {
        $commentaires = Commentaire::where('publication_id', $publication_id)
            ->with('user')
            ->latest('date_commentaire')
            ->get();

        return response()->json($commentaires);
    }

    public function store(Request $request)
    {
        $request->validate([
            'publication_id' => 'required|exists:publications,id',
            'contenu' => 'required|string',
        ]);

        $commentaire = Commentaire::create([
            'publication_id' => $request->publication_id,
            'user_id' => Auth::id(),
            'contenu' => $request->contenu,
            'date_commentaire' => now(),
        ]);

        return response()->json($commentaire->load('user'), 201);
    }

    public function destroy($id)
    {
        $commentaire = Commentaire::findOrFail($id);

        if ($commentaire->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $commentaire->delete();

        return response()->json(['message' => 'Commentaire supprimé']);
    }
    // Ajouter cette méthode pour lister tous les commentaires
public function all()
{
    $commentaires = Commentaire::with(['user', 'publication'])
        ->latest('date_commentaire')
        ->get();

    return response()->json($commentaires);
}

// Ajouter cette méthode pour modifier un commentaire
public function update(Request $request, $id)
{
    $commentaire = Commentaire::findOrFail($id);

    // Vérifier que l'utilisateur est le propriétaire
    if ($commentaire->user_id !== Auth::id()) {
        return response()->json(['message' => 'Non autorisé'], 403);
    }

    $request->validate([
        'contenu' => 'required|string'
    ]);

    $commentaire->update([
        'contenu' => $request->contenu
    ]);

    return response()->json([
        'message' => 'Commentaire modifié avec succès',
        'commentaire' => $commentaire->load('user')
    ]);
}
}
