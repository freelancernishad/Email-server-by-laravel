<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_key',
        'from_email',
        'to_email',
        'subject',
        'body',
        'cc',
        'bcc',
        'reply_to',
        'status',
        'error_message',
        'ip_address',
    ];
}
