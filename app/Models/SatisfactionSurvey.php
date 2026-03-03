<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatisfactionSurvey extends Model
{
    protected $table = 'satisfaction_surveys';
    protected $fillable = ['tenant_id', 'contact_id', 'conversation_id', 'ticket_id', 'type', 'score', 'comment'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }
    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
}
