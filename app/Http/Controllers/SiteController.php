<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SiteController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:150']], [
            'email.required' => 'Escribe tu correo para suscribirte.',
            'email.email' => 'Ingresa un correo válido.',
        ]);

        $sub = Subscriber::firstOrCreate(['email' => strtolower($data['email'])]);

        return redirect()->to(url()->previous().'#boletin')->with(
            'success',
            $sub->wasRecentlyCreated ? '¡Listo! Te suscribiste a nuestras novedades.' : 'Ese correo ya estaba suscrito.'
        );
    }

    public function contact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100', "regex:/^[\pL\s.'-]+$/u"],
            'email' => ['required', 'email:rfc', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'name.required' => 'Escribe tu nombre.',
            'name.regex' => 'El nombre solo puede contener letras.',
            'email.email' => 'Ingresa un correo válido.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
        ]);

        try {
            Mail::raw("De: {$data['name']} <{$data['email']}>\n\n{$data['message']}", function ($m) use ($data) {
                $m->to(config('hotel.email'))->replyTo($data['email'], $data['name'])->subject('Consulta desde la web — '.$data['name']);
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()->to(route('home').'#contacto')->withInput()->with('error', 'No pudimos enviar tu mensaje. Inténtalo de nuevo más tarde.');
        }

        return redirect()->to(route('home').'#contacto')->with('success', 'Mensaje enviado. Te responderemos pronto.');
    }
}