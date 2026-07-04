<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFoodCategoryRequest;
use App\Http\Requests\Admin\UpdateFoodCategoryRequest;
use App\Models\FoodCategory;
use App\Services\AdminNotificationService;
use App\Traits\Loggable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FoodCategoryController extends Controller
{
    use Loggable;

    public function index(Request $request)
    {
        $query = FoodCategory::query();

        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where('name', 'like', "%{$keyword}%");
        }

        $categories = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.foods.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.foods.categories.create');
    }

    public function store(StoreFoodCategoryRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']);

        $category = FoodCategory::create($data);

        AdminNotificationService::push(
            '🍿 Thêm danh mục',
            'Đã thêm danh mục ' . $category->name,
            'Success'
        );

        return redirect()
            ->route('admin.foods.categories.index')
            ->with('success', 'Danh mục đã được tạo.');
    }

    public function edit(FoodCategory $category)
    {
        return view('admin.foods.categories.edit', compact('category'));
    }

    public function update(UpdateFoodCategoryRequest $request, FoodCategory $category)
    {
        $data = $request->validated();

        // 🔥 update slug khi đổi name
        if ($category->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return redirect()
            ->route('admin.foods.categories.index')
            ->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(FoodCategory $category)
    {
        // 🔥 chặn xoá nếu đang có food
        if ($category->foods()->exists()) {
            return back()->with('error', 'Không thể xóa vì danh mục đang có sản phẩm.');
        }

        $category->delete();

        return back()->with('success', 'Danh mục đã được xóa.');
    }
}