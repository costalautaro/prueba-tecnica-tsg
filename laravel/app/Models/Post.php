<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Post de prueba."),
 *     @OA\Property(property="content", type="string", example="Contenido del post de prueba."),
 *     @OA\Property(property="user_id", type="integer", example=1)
 * )
 */
class Post extends Model
{
    use HasFactory;
    //


    protected $fillable = [
        'user_id',
        'title',
        'content',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
