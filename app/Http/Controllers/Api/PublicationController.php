<?php

namespace App\Http\Controllers\Api;

use App\Models\Publication;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PublicationController extends Controller
{
    public function index()
    {
        $publications = Publication::with(['user', 'commentaires.user', 'likes'])
            ->latest('date_publication')
            ->get();

        return response()->json($publications);
    }

    public function store(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $publication = Publication::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'date_publication' => now(),
        ]);

        return response()->json($publication, 201);
    }

    public function destroy($id)
    {
        $publication = Publication::findOrFail($id);

        if ($publication->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $publication->delete();

        return response()->json(['message' => 'Publication supprimée']);
    }
    // Ajouter cette méthode pour voir une publication spécifique (optionnel)
public function show($id)
{
    $publication = Publication::with(['user', 'commentaires.user', 'likes'])
        ->findOrFail($id);

    return response()->json($publication);
}

// Ajouter cette méthode pour modifier une publication
public function update(Request $request, $id)
{
    $publication = Publication::findOrFail($id);

    // Vérifier que l'utilisateur est le propriétaire
    if ($publication->user_id !== Auth::id()) {
        return response()->json(['message' => 'Non autorisé'], 403);
    }

    $request->validate([
        'message' => 'required|string'
    ]);

    $publication->update([
        'message' => $request->message
    ]);

    return response()->json([
        'message' => 'Publication modifiée avec succès',
        'publication' => $publication->load('user')
    ]);
}
}
