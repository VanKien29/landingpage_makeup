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
        $request->validate(['status' => ['nullable', Rule::in(['pending', 'confirmed', 'waitlisted', 'cancelled'])], 'search' => ['nullable', 'string', 'max:150']]);
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

        return view('admin.workshop', ['workshop' => $workshop, 'registrations' => $query->paginate(20)->withQueryString(), 'totalRegistrations' => $workshop->registrations()->count(), 'pendingTickets' => $workshop->registrations()->where('status', WorkshopRegistration::Pending)->count(), 'waitlistedTickets' => $workshop->registrations()->where('status', WorkshopRegistration::Waitlisted)->count(), 'checkedInTickets' => $workshop->registrations()->whereNotNull('checked_in_at')->count(), 'images' => UpdateWorkshopEventRequest::imageOptions(), 'experienceLabels' => WorkshopRegistration::ExperienceLabels]);
    }

    public function updateEvent(UpdateWorkshopEventRequest $request, WorkshopEvent $workshop): RedirectResponse
    {
        DB::transaction(function () use ($request, $workshop): void {
            $event = WorkshopEvent::lockForUpdate()->findOrFail($workshop->id);
            if ($request->integer('capacity') < $event->confirmed_tickets) {
                throw ValidationException::withMessages(['capacity' => 'Sức chứa không được nhỏ hơn số học viên đã xác nhận.']);
            }
            $event->update([...$request->validated(), 'ticket_price' => 0]);
        }, 3);

        return back()->with('success', 'Đã cập nhật thông tin workshop.');
    }

    public function updateRegistration(Request $request, WorkshopEvent $workshop, WorkshopRegistration $registration): RedirectResponse
    {
        abort_unless($registration->workshop_event_id === $workshop->id, 404);
        $data = $request->validate(['action' => ['required', Rule::in(['confirm', 'cancel', 'pending', 'waitlist', 'check-in', 'undo-check-in'])]]);

        DB::transaction(function () use ($data, $workshop, $registration): void {
            $event = WorkshopEvent::lockForUpdate()->findOrFail($workshop->id);
            $entry = $event->registrations()->lockForUpdate()->findOrFail($registration->id);
            if (in_array($data['action'], ['check-in', 'undo-check-in'], true)) {
                if ($entry->status !== WorkshopRegistration::Confirmed) {
                    throw ValidationException::withMessages(['action' => 'Chỉ học viên đã được xác nhận tham dự mới được check-in.']);
                }
                $entry->checked_in_at = $data['action'] === 'check-in' ? ($entry->checked_in_at ?? now()) : null;
            } elseif ($data['action'] === 'confirm') {
                if ($entry->status !== WorkshopRegistration::Confirmed) {
                    if ($entry->quantity > $event->remainingTickets()) {
                        throw ValidationException::withMessages(['action' => 'Đã đủ chỗ. Không thể xác nhận vượt giới hạn học viên; vui lòng giữ đăng ký trong danh sách chờ.']);
                    }
                    $event->increment('confirmed_tickets', $entry->quantity);
                    $entry->status = WorkshopRegistration::Confirmed;
                    $entry->confirmed_at = now();
                }
            } else {
                if ($entry->status === WorkshopRegistration::Confirmed) {
                    $event->decrement('confirmed_tickets', $entry->quantity);
                }
                $entry->status = match ($data['action']) {
                    'cancel' => WorkshopRegistration::Cancelled,
                    'waitlist' => WorkshopRegistration::Waitlisted,
                    default => $event->remainingTickets() > 0 ? WorkshopRegistration::Pending : WorkshopRegistration::Waitlisted,
                };
                $entry->checked_in_at = null;
                $entry->confirmed_at = null;
            }
            $entry->save();
        }, 3);

        return back()->with('success', 'Đã cập nhật trạng thái tham dự. Ban tổ chức chủ động liên hệ học viên để thông báo xác nhận.');
    }

    public function export(WorkshopEvent $workshop): StreamedResponse
    {
        return response()->streamDownload(function () use ($workshop): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Mã đăng ký', 'Họ tên', 'Điện thoại tại Nhật', 'Email', 'Mạng xã hội', 'Tình trạng hiện tại', 'Cam kết tham dự', 'Trạng thái', 'Xác nhận lúc', 'Check-in lúc'], escape: '');
            foreach ($workshop->registrations()->orderBy('id')->cursor() as $entry) {
                $row = [$entry->reference, $entry->name, $entry->phone, $entry->email, $entry->social, WorkshopRegistration::ExperienceLabels[$entry->experience] ?? '', $entry->attendance_confirmed ? 'Có' : 'Chưa xác nhận', $entry->status, $entry->confirmed_at?->toIso8601String(), $entry->checked_in_at?->toIso8601String()];
                fputcsv($output, array_map(static fn ($cell): string => preg_match('/^[\s]*[=+@-]/u', (string) $cell) ? "'".(string) $cell : (string) $cell, $row), escape: '');
            }
            fclose($output);
        }, $workshop->slug.'-guests.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
