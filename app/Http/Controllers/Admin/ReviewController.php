<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MovieReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = MovieReview::with(['movie', 'user'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Request $request, MovieReview $review)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,hidden'],
        ]);

        $review->update($data);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'moderate_review',
            'module' => 'reviews',
            'description' => 'Cap nhat trang thai danh gia #' . $review->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'properties' => $data,
        ]);

        return back()->with('success', 'Da cap nhat danh gia.');
    }

    public function destroy(Request $request, MovieReview $review)
    {
        $reviewId = $review->id;
        $review->delete();

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'delete_review',
            'module' => 'reviews',
            'description' => 'Xoa danh gia #' . $reviewId,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'Da xoa danh gia.');
    }
}
