<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWorkshopEventRequest;
use App\Models\WorkshopEvent;
use App\Models\WorkshopRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkshopAdminController extends Controller
{
    public function index(Request $request, WorkshopEvent $workshop): View
    {
        $request->validate(['status' => ['nullable', Rule::in(['pending', 'confirmed', 'cancelled'])], 'search' => ['nullable', 'string', 'max:150']]);
        $query = $workshop->registrations()->latest()->orderByDesc('id');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->trim().'%';
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('reference', 'like', $search);
            });
        }

        return view('admin.workshop', ['workshop' => $workshop, 'registrations' => $query->paginate(20)->withQueryString(), 'pendingTickets' => $workshop->registrations()->where('status', WorkshopRegistration::Pending)->sum('quantity'), 'checkedInTickets' => $workshop->registrations()->whereNotNull('checked_in_at')->sum('quantity'), 'images' => UpdateWorkshopEventRequest::imageOptions()]);
    }

    public function updateEvent(UpdateWorkshopEventRequest $request, WorkshopEvent $workshop): RedirectResponse
    {
        DB::transaction(function () use ($request, $workshop): void {
            $event = WorkshopEvent::lockForUpdate()->findOrFail($workshop->id);
            if ($request->integer('capacity') < $event->confirmed_tickets) {
                throw ValidationException::withMessages(['capacity' => 'Sức chứa không được nhỏ hơn số vé đã xác nhận.']);
            }
            $event->update($request->validated());
        }, 3);

        return back()->with('success', 'Đã cập nhật thông tin workshop.');
    }

    public function updateRegistration(Request $request, WorkshopEvent $workshop, WorkshopRegistration $registration): RedirectResponse
    {
        abort_unless($registration->workshop_event_id === $workshop->id, 404);
        $data = $request->validate(['action' => ['required', Rule::in(['confirm', 'cancel', 'pending', 'check-in', 'undo-check-in'])], 'bank_reference' => ['required_if:action,confirm', 'nullable', 'string', 'max:120']]);

        DB::transaction(function () use ($data, $workshop, $registration): void {
            $event = WorkshopEvent::lockForUpdate()->findOrFail($workshop->id);
            $entry = $event->registrations()->lockForUpdate()->findOrFail($registration->id);
            if (in_array($data['action'], ['check-in', 'undo-check-in'], true)) {
                if ($entry->status !== WorkshopRegistration::Confirmed) {
                    throw ValidationException::withMessages(['action' => 'Chỉ vé đã thanh toán và xác nhận mới được check-in.']);
                }
                $entry->checked_in_at = $data['action'] === 'check-in' ? ($entry->checked_in_at ?? now()) : null;
            } elseif ($data['action'] === 'confirm') {
                if ($entry->status !== WorkshopRegistration::Confirmed) {
                    if ($entry->quantity > $event->remainingTickets()) {
                        throw ValidationException::withMessages(['action' => 'Không đủ chỗ. Không thể xác nhận vượt giới hạn vé.']);
                    }
                    $event->increment('confirmed_tickets', $entry->quantity);
                    $entry->status = WorkshopRegistration::Confirmed;
                    $entry->paid_at = now();
                    $entry->bank_reference = $data['bank_reference'];
                }
            } else {
                if ($entry->status === WorkshopRegistration::Confirmed) {
                    $event->decrement('confirmed_tickets', $entry->quantity);
                }
                $entry->status = $data['action'] === 'cancel' ? WorkshopRegistration::Cancelled : WorkshopRegistration::Pending;
                $entry->checked_in_at = null;
                $entry->paid_at = null;
                $entry->bank_reference = null;
            }
            $entry->save();
        }, 3);

        return back()->with('success', 'Đã cập nhật đăng ký. Thay đổi trạng thái tại đây không thực hiện hoàn tiền qua ngân hàng.');
    }

    public function export(WorkshopEvent $workshop): StreamedResponse
    {
        return response()->streamDownload(function () use ($workshop): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Mã đăng ký', 'Họ tên', 'Điện thoại', 'Email', 'Mạng xã hội', 'Số vé', 'Đơn giá JPY', 'Tổng JPY', 'Trạng thái', 'Mã giao dịch', 'Thanh toán lúc', 'Check-in lúc', 'Ghi chú'], escape: '');
            foreach ($workshop->registrations()->orderBy('id')->cursor() as $entry) {
                $row = [$entry->reference, $entry->name, $entry->phone, $entry->email, $entry->social, $entry->quantity, $entry->unit_price, $entry->total(), $entry->status, $entry->bank_reference, $entry->paid_at?->toIso8601String(), $entry->checked_in_at?->toIso8601String(), $entry->notes];
                fputcsv($output, array_map(static fn ($cell): string => preg_match('/^[\s]*[=+@-]/u', (string) $cell) ? "'".(string) $cell : (string) $cell, $row), escape: '');
            }
            fclose($output);
        }, $workshop->slug.'-guests.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
