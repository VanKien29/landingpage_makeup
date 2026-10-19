<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PortfolioController extends Controller
{
    public function index(): View
    {
        return view('admin.portfolio', ['portfolioItems' => PortfolioItem::orderBy('display_order')->paginate(18)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['bridal', 'beauty', 'douyin', 'creative', 'editorial'])],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'display_order' => ['required', 'integer', 'min:0', 'max:10000'],
        ], [
            'title.required' => 'Vui lòng nhập tên tác phẩm.',
            'category.in' => 'Phong cách không hợp lệ.',
            'image.required' => 'Vui lòng chọn hình ảnh.',
            'image.image' => 'Tệp tải lên phải là hình ảnh.',
            'image.mimes' => 'Chỉ hỗ trợ ảnh JPG, PNG hoặc WebP.',
            'image.max' => 'Ảnh tối đa 8 MB (hoặc giới hạn tải lên của máy chủ).',
        ]);

        $path = $request->file('image')->store('portfolio', 'local');
        abort_unless($path, 500, 'Không thể lưu ảnh. Vui lòng thử lại.');

        try {
            PortfolioItem::create([
                'title' => $data['title'],
                'category' => $data['category'],
                'alt_text' => $data['alt_text'] ?? $data['title'],
                'image_path' => $path,
                'display_order' => $data['display_order'],
                'is_featured' => true,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return redirect()->route('admin.portfolio')->with('success', 'Đã thêm tác phẩm vào bộ sưu tập.');
    }

    public function show(PortfolioItem $portfolioItem): BinaryFileResponse
    {
        abort_unless($portfolioItem->is_featured && str_starts_with($portfolioItem->image_path, 'portfolio/'), 404);
        abort_unless(Storage::disk('local')->exists($portfolioItem->image_path), 404);

        return response()->file(Storage::disk('local')->path($portfolioItem->image_path), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function destroy(PortfolioItem $portfolioItem): RedirectResponse
    {
        $path = $portfolioItem->image_path;
        $portfolioItem->delete();

        if (str_starts_with($path, 'portfolio/')) {
            Storage::disk('local')->delete($path);
        }

        return redirect()->route('admin.portfolio')->with('success', 'Đã xóa tác phẩm khỏi bộ sưu tập.');
    }
}
