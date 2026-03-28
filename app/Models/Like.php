<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['publication_id', 'user_id'];
    
    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Ajouter ou retirer un like (toggle)
     */
    public static function ajouterLikes($publicationId, $userId)
    {
        $like = self::where('publication_id', $publicationId)
                    ->where('user_id', $userId)
                    ->first();
        
        if ($like) {
            // Si le like existe déjà, on le supprime
            $like->delete();
            $action = 'unliked';
        } else {
            // Sinon, on crée le like
            $like = self::create([
                'publication_id' => $publicationId,
                'user_id' => $userId
            ]);
            $action = 'liked';
        }
        
        $likesCount = self::where('publication_id', $publicationId)->count();
        
        return [
            'action' => $action,
            'likes' => $likesCount
        ];
    }
}
