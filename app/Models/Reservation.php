<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    /**
     * Relación inversa: Una reservación pertenece a una habitación.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relación inversa: Una reservación pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['cancelled_at' => 'datetime'];
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /** Horas de antelación mínimas para que un huésped cancele por su cuenta. */
    public const FREE_CANCEL_HOURS = 24;

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /** Límite hasta el que el huésped puede cancelar (entrada a las 14:00 menos el plazo). */
    public function cancelDeadline(): \Carbon\Carbon
    {
        return \Carbon\Carbon::parse($this->check_in)->setTime(14, 0)->subHours(self::FREE_CANCEL_HOURS);
    }

    public function guestCanCancel(): bool
    {
        return ! $this->isCancelled() && now()->lte($this->cancelDeadline());
    }

    /** El administrador puede cancelar mientras la estancia no haya terminado. */
    public function adminCanCancel(): bool
    {
        return ! $this->isCancelled() && \Carbon\Carbon::parse($this->check_out)->endOfDay()->gte(now());
    }

    public function canBeCancelledBy(User $user): bool
    {
        return $user->is_admin ? $this->adminCanCancel() : $this->guestCanCancel();
    }
}