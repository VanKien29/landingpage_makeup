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
        return view('workshop', ['workshop' => $workshop, 'remaining' => $workshop->remainingTickets(), 'concepts' => config('workshop.concepts'), 'schedule' => config('workshop.schedule'), 'benefits' => config('workshop.benefits')]);
    }

    public function store(StoreWorkshopRegistrationRequest $request, WorkshopEvent $workshop): JsonResponse|RedirectResponse
    {
        $registration = DB::transaction(function () use ($request, $workshop): WorkshopRegistration {
            $event = WorkshopEvent::lockForUpdate()->findOrFail($workshop->id);
            if (! $event->acceptsRegistrations()) {
                throw ValidationException::withMessages(['quantity' => 'Workshop đã đóng đăng ký. Vui lòng liên hệ học viện để được hỗ trợ.']);
            }
            if ($request->integer('quantity') > $event->remainingTickets()) {
                throw ValidationException::withMessages(['quantity' => 'Số lượng yêu cầu vượt số vé còn có thể xác nhận. Vui lòng giảm số vé.']);
            }

            $registration = new WorkshopRegistration($request->safe()->only(['name', 'phone', 'email', 'social', 'quantity', 'notes']));
            $registration->reference = 'ELEV-'.Str::upper(Str::random(10));
            $registration->unit_price = $event->ticket_price;
            $registration->status = WorkshopRegistration::Pending;
            $registration->consented_at = now();
            $event->registrations()->save($registration);

            return $registration;
        }, 3);

        $message = 'Đã nhận đăng ký '.$registration->reference.' cho '.$registration->quantity.' vé, tổng ¥'.number_format($registration->total()).'. Học viện sẽ liên hệ qua thông tin bạn cung cấp để hướng dẫn chuyển khoản. Vé chỉ được giữ chỗ sau khi xác nhận thanh toán.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'reference' => $registration->reference], 201);
        }

        return redirect()->to(route('workshop.show', $workshop->slug).'#registration')->with('workshop_success', $message);
    }
}
