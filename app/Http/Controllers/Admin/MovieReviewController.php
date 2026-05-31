<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Movie;
use App\Models\MovieReview;
use Illuminate\Http\Request;

class MovieReviewController extends Controller
{
    public function index()
    {
        $reviews = MovieReview::with('movie', 'user')->latest()->paginate(12);
        $movies = Movie::orderBy('title')->get(['id', 'title']);

        return view('admin.movie-reviews.index', compact('reviews', 'movies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'movie_id' => ['required', 'exists:movies,id'],
            'reviewer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,approved,hidden'],
        ]);

        $review = MovieReview::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'module' => 'movie_reviews',
            'description' => 'Them danh gia phim #' . $review->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Da them danh gia phim.');
    }

    public function update(Request $request, MovieReview $movieReview)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,hidden'],
        ]);

        $movieReview->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'module' => 'movie_reviews',
            'description' => 'Cap nhat danh gia phim #' . $movieReview->id . ' sang ' . $data['status'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Da cap nhat trang thai danh gia.');
    }

    public function destroy(Request $request, MovieReview $movieReview)
    {
        $id = $movieReview->id;
        $movieReview->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'module' => 'movie_reviews',
            'description' => 'Xoa danh gia phim #' . $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Da xoa danh gia phim.');
    }
}
