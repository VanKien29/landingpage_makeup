<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function index(): View
    {
        $enrollments = Enrollment::latest()->paginate(20);

        return view('admin.enrollments', compact('enrollments'));
    }
}
