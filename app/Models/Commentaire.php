<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    protected $fillable = ['publication_id', 'user_id', 'contenu', 'date_commentaire'];
    
    protected $casts = [
        'date_commentaire' => 'datetime',
    ];
    
    // Méthode pour ajouter un commentaire
    public static function ajouterCommentaire($publicationId, $contenu, $userId)
    {
        return self::create([
            'publication_id' => $publicationId,
            'user_id' => $userId,
            'contenu' => $contenu,
            'date_commentaire' => now(),
        ]);
    }
    
    // Méthode pour supprimer un commentaire
    public function supprimerCommentaire()
    {
        return $this->delete();
    }
    
    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
