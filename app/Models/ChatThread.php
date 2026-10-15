<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatThread extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'admin_id',
        'session_id',
        'auth_token',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Create a unique auth token for the private channel
        static::creating(function ($thread) {
            $thread->auth_token = Str::random(40);
        });
    }

    /**
     * Get the user that this chat thread belongs to.
     */
    public function user()
    {
        // IMPORTANT: 
        // If your customer model is App\Models\User, use User::class.
        // If it's App\Models\Customer, use Customer::class.
        // This must match the model used by Auth::guard('customer')
        
        return $this->belongsTo(\App\Models\User::class, 'user_id');
        
        // --- OR ---
        
        // return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the messages for the chat thread.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_thread_id');
    }

    /**
     * Get the admin that this chat thread is assigned to.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}