<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $reservation = $this->route('reservation');

        abort_unless(
            $user !== null && ($user->is_admin || $reservation?->user_id === $user->id),
            404
        );

        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.string' => 'El motivo de cancelación debe ser texto.',
            'reason.max' => 'El motivo de cancelación no puede superar 200 caracteres.',
        ];
    }
}
