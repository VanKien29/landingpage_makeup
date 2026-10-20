<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEnrollmentStatusRequest;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'message' => 'nullable|string|max:2000',
            'agree' => 'accepted',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'contact.required' => 'Vui lòng nhập thông tin liên hệ (Số điện thoại, Zalo, Line hoặc Email).',
            'agree.accepted' => 'Vui lòng xác nhận đồng ý với điều khoản và chính sách.',
            'name.string' => 'Họ và tên phải là văn bản.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'contact.string' => 'Thông tin liên hệ phải là văn bản.',
            'contact.max' => 'Thông tin liên hệ không được vượt quá 255 ký tự.',
            'message.string' => 'Lời nhắn phải là văn bản.',
            'message.max' => 'Lời nhắn không được vượt quá 2.000 ký tự.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $enrollment = Enrollment::create([
            'name' => $request->input('name'),
            'contact' => $request->input('contact'),
            'message' => $request->input('message'),
            'agreed_terms' => true,
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn '.$enrollment->name.'! Văn Cường Makeup Academy đã nhận được thông tin và sẽ liên hệ tư vấn trong thời gian sớm nhất.',
                'data' => [
                    'id' => $enrollment->id,
                    'created_at' => $enrollment->created_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Đăng ký tư vấn thành công! Chúng tôi sẽ sớm liên hệ với bạn.');
    }

    public function index(Request $request): View
    {
        $filters = $this->listingParameters($request);
        $enrollments = Enrollment::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')->orWhere('contact', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.enrollments', [
            'enrollments' => $enrollments,
            'filters' => $filters,
            'statuses' => Enrollment::StatusLabels,
            'counts' => Enrollment::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(UpdateEnrollmentStatusRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $filters = $this->listingParameters($request);
        $enrollment->update($request->validated());

        return redirect()->route('admin.enrollments', $filters)->with('success', 'Đã cập nhật trạng thái tư vấn của '.$enrollment->name.'.');
    }

    /** @return array<string, mixed> */
    private function listingParameters(Request $request): array
    {
        return Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(Enrollment::StatusLabels))],
            'page' => ['nullable', 'integer', 'min:1'],
        ])->validate();
    }
}
