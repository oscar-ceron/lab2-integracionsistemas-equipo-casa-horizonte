<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $isUpdate = $this->is('api/reservations/*') && in_array($this->method(), ['PUT', 'PATCH'], true);
        $required = $isUpdate ? ['sometimes', 'required'] : ['required'];

        $rules = [
            'room_id' => [...$required, 'integer', Rule::exists('rooms', 'id')],
            'check_in' => [...$required, 'date'],
            'check_out' => [...$required, 'date'],
            // El precio y el estado siempre se determinan en el servidor.
            'total_price' => ['prohibited'],
            'status' => ['prohibited'],
        ];

        if (! $this->is('api/*')) {
            if ($this->user()?->is_admin) {
                $rules += [
                    'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
                    'guest_name' => ['nullable', 'required_with:guest_email', 'string', 'min:3', 'max:100', "regex:/^[\\pL\\s.'-]+$/u"],
                    'guest_email' => ['nullable', 'required_without:user_id', 'email:rfc', 'max:255'],
                ];
            } else {
                $rules += [
                    'user_id' => ['prohibited'],
                    'guest_name' => ['prohibited'],
                    'guest_email' => ['prohibited'],
                ];
            }

            $rules['check_in'][] = 'after_or_equal:today';
            $rules['check_in'][] = 'before_or_equal:'.today()->addYear()->toDateString();
        } else {
            $rules += [
                'user_id' => ['prohibited'],
                'guest_name' => ['prohibited'],
                'guest_email' => ['prohibited'],
            ];
        }

        if (! $isUpdate) {
            $rules['check_out'][] = 'after:check_in';
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->is('api/*') && $this->filled('check_in') && $this->filled('check_out')
                && ! $validator->errors()->has('check_in') && ! $validator->errors()->has('check_out')
                && Carbon::parse($this->input('check_out'))->gt(Carbon::parse($this->input('check_in'))->addDays(30))) {
                $validator->errors()->add('check_out', 'La estadía máxima es de 30 noches.');
            }

            if (! ($this->is('api/reservations/*') && in_array($this->method(), ['PUT', 'PATCH'], true))) {
                return;
            }

            $checkIn = $this->input('check_in');
            $checkOut = $this->input('check_out');

            if ($checkIn && $checkOut && $checkOut <= $checkIn && ! $validator->errors()->has('check_out')) {
                $validator->errors()->add('check_out', 'La fecha de salida debe ser posterior a la fecha de entrada.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Selecciona una habitación.',
            'room_id.integer' => 'La habitación seleccionada no es válida.',
            'room_id.exists' => 'La habitación seleccionada no existe.',
            'check_in.required' => 'La fecha de entrada es obligatoria.',
            'check_in.date' => 'Escribe una fecha de entrada válida.',
            'check_in.after_or_equal' => 'La entrada no puede ser anterior a hoy.',
            'check_in.before_or_equal' => 'Solo se puede reservar con hasta un año de anticipación.',
            'check_out.required' => 'La fecha de salida es obligatoria.',
            'check_out.date' => 'Escribe una fecha de salida válida.',
            'check_out.after' => 'La salida debe ser posterior a la entrada.',
            'check_out.before_or_equal' => 'La estadía máxima es de 30 noches.',
            'user_id.integer' => 'El huésped seleccionado no es válido.',
            'user_id.exists' => 'El huésped seleccionado no existe.',
            'user_id.prohibited' => 'No puedes seleccionar otro huésped.',
            'guest_name.required_with' => 'Indica el nombre del huésped.',
            'guest_name.string' => 'El nombre del huésped no es válido.',
            'guest_name.min' => 'El nombre del huésped debe tener al menos 3 caracteres.',
            'guest_name.max' => 'El nombre del huésped no puede superar 100 caracteres.',
            'guest_name.regex' => 'El nombre solo puede contener letras, espacios y signos de puntuación básicos.',
            'guest_name.prohibited' => 'No puedes indicar otro huésped.',
            'guest_email.required_without' => 'Elige un huésped o escribe su correo.',
            'guest_email.email' => 'Escribe un correo válido.',
            'guest_email.max' => 'El correo no puede superar 255 caracteres.',
            'guest_email.prohibited' => 'No puedes indicar otro huésped.',
            'total_price.prohibited' => 'El precio de la reserva se calcula en el servidor.',
            'status.prohibited' => 'El estado de la reserva se asigna en el servidor.',
        ];
    }
}
