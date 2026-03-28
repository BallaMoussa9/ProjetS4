<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    protected $fillable = ['user_id', 'message', 'date_publication'];
    
    protected $casts = [
        'date_publication' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }
    
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
