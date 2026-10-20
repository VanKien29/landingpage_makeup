<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkshopRegistrationRequest;
use App\Models\WorkshopEvent;
use App\Models\WorkshopRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function show(WorkshopEvent $workshop): View
    {
        return view('workshop', ['workshop' => $workshop, 'remaining' => $workshop->remainingTickets(), 'concepts' => config('workshop.concepts'), 'schedule' => config('workshop.schedule'), 'experienceLabels' => WorkshopRegistration::ExperienceLabels]);
    }

    public function store(StoreWorkshopRegistrationRequest $request, WorkshopEvent $workshop): JsonResponse|RedirectResponse
    {
        $registration = DB::transaction(function () use ($request, $workshop): WorkshopRegistration {
            $event = WorkshopEvent::lockForUpdate()->findOrFail($workshop->id);
            if (! $event->acceptsRegistrations()) {
                throw ValidationException::withMessages(['attendance' => 'Workshop đã đóng đăng ký. Vui lòng liên hệ học viện để được hỗ trợ.']);
            }
            if ($event->registrations()->where('email', $request->validated('email'))->where('status', '!=', WorkshopRegistration::Cancelled)->exists()) {
                throw ValidationException::withMessages(['email' => 'Email này đã có đăng ký cho workshop. Vui lòng chờ ban tổ chức xác nhận hoặc liên hệ để cập nhật thông tin.']);
            }

            $registration = new WorkshopRegistration($request->safe()->only(['name', 'phone', 'email', 'social', 'experience']));
            $registration->reference = 'BACK-'.Str::upper(Str::random(10));
            $registration->quantity = 1;
            $registration->unit_price = 0;
            $registration->attendance_confirmed = true;
            $registration->consent_version = 'the-back-2026-v1';
            $registration->status = $event->remainingTickets() > 0 ? WorkshopRegistration::Pending : WorkshopRegistration::Waitlisted;
            $registration->consented_at = now();
            $event->registrations()->save($registration);

            return $registration;
        }, 3);

        $message = $registration->status === WorkshopRegistration::Waitlisted
            ? 'Đã nhận đăng ký '.$registration->reference.' vào danh sách chờ của THE BACK. Hiện các suất tham dự đã được xác nhận đủ. Ban tổ chức sẽ liên hệ nếu có chỗ trống. Workshop hoàn toàn miễn phí.'
            : 'Cảm ơn bạn! Đã tiếp nhận đăng ký '.$registration->reference.' cho THE BACK. Workshop hoàn toàn miễn phí. Vui lòng chờ ban tổ chức liên hệ qua điện thoại, email hoặc mạng xã hội để xác nhận suất tham dự và thông báo ngày, địa điểm cụ thể.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'reference' => $registration->reference, 'status' => $registration->status], 201);
        }

        return redirect()->to(route('workshop.show', $workshop->slug).'#registration')->with('workshop_success', $message);
    }
}
