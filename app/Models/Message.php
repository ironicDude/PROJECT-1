<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['text', 'file', 'sender_id', 'receiver_id'];


    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
