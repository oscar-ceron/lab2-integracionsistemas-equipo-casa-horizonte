<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // Un comentario por usuario cada 10 minutos para evitar spam.
        if ($request->user()->reviews()->where('created_at', '>', now()->subMinutes(10))->exists()) {
            return back()->withInput()->with('error', 'Espera unos minutos antes de publicar otro comentario.');
        }

        $request->user()->reviews()->create($data);

        return redirect()->to(route('home').'#comentarios')->with('success', '¡Gracias por tu comentario!');
    }

    public function destroy(Request $request, Review $review)
    {
        abort_unless($request->user()->is_admin || $review->user_id === $request->user()->id, 403);
        $review->delete();

        return redirect()->to(route('home').'#comentarios')->with('success', 'Comentario eliminado.');
    }
}