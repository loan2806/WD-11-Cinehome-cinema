<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Phim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieReviewController extends Controller
{
    public function store(Request $request, Phim $movie)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:1000'],
        ]);

        $movie->reviews()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'rating' => $data['rating'],
                'content' => $data['content'] ?? null,
                'status' => 'approved',
            ]
        );

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'review_movie',
            'module' => 'reviews',
            'description' => 'Danh gia phim ' . $movie->title,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'properties' => ['movie_id' => $movie->id, 'rating' => $data['rating']],
        ]);

        return back()->with('success', 'Da gui danh gia phim.');
    }
}
