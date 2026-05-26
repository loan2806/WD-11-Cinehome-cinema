<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->module))
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = '%' . $request->keyword . '%';

                $query->where(function ($inner) use ($keyword) {
                    $inner->where('action', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = ActivityLog::query()->whereNotNull('module')->distinct()->pluck('module');

        return view('admin.activity-logs.index', compact('logs', 'modules'));
    }
}
