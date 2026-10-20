<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePortfolioCategoryRequest;
use App\Http\Requests\UpdatePortfolioCategoryRequest;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Services\PortfolioCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPortfolioCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:80']]);

        return view('admin.portfolio-categories', ['categories' => PortfolioCategory::withCount('portfolioItems')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('slug', 'like', '%'.$search.'%')))
            ->orderBy('display_order')->orderBy('name')->get()]);
    }

    public function store(StorePortfolioCategoryRequest $request): RedirectResponse
    {
        PortfolioCategory::create([...$request->validated(), 'is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Đã thêm danh mục.');
    }

    public function edit(PortfolioCategory $category): View
    {
        return view('admin.portfolio-category-edit', ['category' => $category]);
    }

    public function update(UpdatePortfolioCategoryRequest $request, PortfolioCategory $category): RedirectResponse
    {
        $data = $request->validated();
        $oldSlug = $category->slug;
        $data['is_active'] = $request->boolean('is_active');
        DB::transaction(function () use ($category, $data, $oldSlug): void {
            $category->update($data);
            if ($oldSlug !== $category->slug) {
                PortfolioItem::where('category', $oldSlug)->update(['category' => $category->slug]);
            }
        });
        PortfolioCache::flush();

        return redirect()->route('admin.portfolio.categories')->with('success', 'Đã cập nhật danh mục.');
    }

    public function destroy(PortfolioCategory $category): RedirectResponse
    {
        if ($category->portfolioItems()->exists()) {
            return back()->withErrors(['category' => 'Không thể xóa danh mục đang có tác phẩm. Hãy chuyển hoặc xóa các tác phẩm trước.']);
        }
        $category->delete();

        return back()->with('success', 'Đã xóa danh mục.');
    }
}
