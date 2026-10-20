<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkPortfolioItemRequest;
use App\Http\Requests\StorePortfolioItemRequest;
use App\Http\Requests\UpdatePortfolioItemRequest;
use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use App\Services\PortfolioCache;
use App\Services\WebpImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PortfolioController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $filters = $this->listingParameters($request);
        $items = $this->filteredItems($filters)->paginate($filters['per_page'] ?? 24)->withQueryString();
        if ($items->currentPage() > $items->lastPage()) {
            return redirect()->route('admin.portfolio', [...$filters, 'page' => $items->lastPage()]);
        }

        return view('admin.portfolio', [
            'portfolioItems' => $items,
            'categories' => PortfolioCategory::orderBy('display_order')->orderBy('name')->get(),
            'filters' => $filters,
            'counts' => [
                'total' => PortfolioItem::count(),
                'visible' => PortfolioItem::where('is_featured', true)->count(),
                'hidden' => PortfolioItem::where('is_featured', false)->count(),
            ],
        ]);
    }

    public function edit(Request $request, PortfolioItem $portfolioItem): View
    {
        return view('admin.portfolio-edit', [
            'portfolioItem' => $portfolioItem,
            'filters' => $this->listingParameters($request),
            'categories' => PortfolioCategory::where('is_active', true)->orWhere('slug', $portfolioItem->category)->orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePortfolioItemRequest $request, WebpImages $images): RedirectResponse
    {
        $filters = $this->listingParameters($request);
        $data = $request->validated();
        $uploads = $request->file('images', []);
        $uploads = is_array($uploads) ? $uploads : [$uploads];
        if ($uploads === [] && $request->hasFile('image')) {
            $uploads = [$request->file('image')];
        }
        $storedPaths = [];
        try {
            DB::transaction(function () use ($uploads, $data, $images, $request, &$storedPaths): void {
                foreach ($uploads as $index => $upload) {
                    $path = $images->storeUpload($upload, $request->hasFile('images') ? 'images.'.$index : 'image');
                    $storedPaths[] = $path;
                    $metadata = $data['items'][$index] ?? $data;
                    PortfolioItem::create([
                        'title' => $metadata['title'],
                        'category' => $metadata['category'],
                        'alt_text' => ($metadata['alt_text'] ?? null) ?: $metadata['title'],
                        'image_path' => $path,
                        'display_order' => $metadata['display_order'],
                        'is_featured' => $metadata['is_featured'] ?? true,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        PortfolioCache::flush();

        return redirect()->route('admin.portfolio', $filters)->with('success', count($storedPaths).' tác phẩm đã được thêm vào bộ sưu tập.');
    }

    public function update(UpdatePortfolioItemRequest $request, PortfolioItem $portfolioItem, WebpImages $images): RedirectResponse
    {
        $filters = $this->listingParameters($request);
        $data = $request->validated();
        $oldPath = $portfolioItem->image_path;
        if ($request->hasFile('image')) {
            $data['image_path'] = $images->storeUpload($request->file('image'), 'image');
        }
        unset($data['image']);
        $data['alt_text'] = ($data['alt_text'] ?? null) ?: $data['title'];
        $data['is_featured'] = $data['is_featured'] ?? false;
        try {
            DB::transaction(fn (): bool => $portfolioItem->update($data));
        } catch (Throwable $exception) {
            if (isset($data['image_path'])) {
                Storage::disk('local')->delete($data['image_path']);
            }
            throw $exception;
        }
        if (isset($data['image_path']) && str_starts_with($oldPath, 'portfolio/')) {
            Storage::disk('local')->delete($oldPath);
        }
        PortfolioCache::flush();

        return $this->redirectToListing($filters)->with('success', 'Đã cập nhật tác phẩm.');
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

    public function preview(PortfolioItem $portfolioItem): BinaryFileResponse
    {
        abort_unless(str_starts_with($portfolioItem->image_path, 'portfolio/') && Storage::disk('local')->exists($portfolioItem->image_path), 404);

        return response()->file(Storage::disk('local')->path($portfolioItem->image_path), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ])->setPrivate();
    }

    public function destroy(Request $request, PortfolioItem $portfolioItem): RedirectResponse
    {
        $filters = $this->listingParameters($request);
        $path = $portfolioItem->image_path;
        $portfolioItem->delete();
        if (str_starts_with($path, 'portfolio/')) {
            Storage::disk('local')->delete($path);
        }

        return $this->redirectToListing($filters)->with('success', 'Đã xóa tác phẩm khỏi bộ sưu tập.');
    }

    public function bulk(BulkPortfolioItemRequest $request): RedirectResponse
    {
        $filters = $this->listingParameters($request);
        $data = $request->validated();
        $paths = [];
        $count = DB::transaction(function () use ($data, &$paths): int {
            $items = PortfolioItem::whereKey($data['ids'])->lockForUpdate()->get();
            foreach ($items as $item) {
                if ($data['action'] === 'delete') {
                    if (str_starts_with($item->image_path, 'portfolio/')) {
                        $paths[] = $item->image_path;
                    }
                    $item->delete();
                } else {
                    $item->update(['is_featured' => $data['action'] === 'show']);
                }
            }

            return $items->count();
        });
        Storage::disk('local')->delete($paths);
        PortfolioCache::flush();
        $label = ['delete' => 'xóa', 'show' => 'hiển thị', 'hide' => 'ẩn'][$data['action']];

        return $this->redirectToListing($filters)->with('success', 'Đã '.$label.' '.$count.' tác phẩm.');
    }

    /** @return array<string, mixed> */
    private function listingParameters(Request $request): array
    {
        return Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:80'],
            'visibility' => ['nullable', Rule::in(['visible', 'hidden'])],
            'sort' => ['nullable', Rule::in(['order', 'newest', 'oldest', 'title'])],
            'per_page' => ['nullable', 'integer', Rule::in([24, 48, 96])],
            'page' => ['nullable', 'integer', 'min:1'],
        ])->validate();
    }

    /** @param array<string, mixed> $filters */
    private function filteredItems(array $filters): Builder
    {
        $query = PortfolioItem::query();
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (! empty($filters['search'])) {
            $query->where(function (Builder $query) use ($filters): void {
                $query->where('title', 'like', '%'.$filters['search'].'%')->orWhere('alt_text', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['visibility'])) {
            $query->where('is_featured', $filters['visibility'] === 'visible');
        }
        match ($filters['sort'] ?? 'order') {
            'newest' => $query->orderByDesc('created_at'),
            'oldest' => $query->orderBy('created_at'),
            'title' => $query->orderBy('title'),
            default => $query->orderBy('display_order'),
        };

        return $query->orderByDesc('id');
    }

    /** @param array<string, mixed> $filters */
    private function redirectToListing(array $filters): RedirectResponse
    {
        if (isset($filters['page'])) {
            $lastPage = max(1, (int) ceil($this->filteredItems($filters)->count() / ($filters['per_page'] ?? 24)));
            $filters['page'] = min($filters['page'], $lastPage);
        }

        return redirect()->route('admin.portfolio', $filters);
    }
}
