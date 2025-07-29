<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'subject',
        'body',
        'status',
        'error_message',
        'attachments',
        'sent_by',
        'encuesta_id'
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
