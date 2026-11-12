<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del huésped es obligatorio.',
            'user_id.exists' => 'El huésped seleccionado no existe.',
            'room_id.required' => 'El ID de la habitación es obligatorio.',
            'room_id.exists' => 'La habitación seleccionada no existe.',
            'check_in.required' => 'La fecha de entrada es obligatoria.',
            'check_out.required' => 'La fecha de salida es obligatoria.',
            'check_out.after' => 'La salida debe ser posterior a la entrada.',
            'total_price.required' => 'El total de la estancia es obligatorio.',
            'total_price.min' => 'El total no puede ser negativo.',
            'status.required' => 'Selecciona el estado de la reserva.',
        ];
    }
}
