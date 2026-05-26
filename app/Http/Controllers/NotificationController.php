<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = $this->notificationService->getUserNotificationsPaginated(auth()->user(), 15);
        return view('notification.notification-list', compact('notifications'));
    }

    public function ajax()
    {
        $notifications = $this->notificationService->getRecentUserNotifications(auth()->user(), 20);
        return view('notification.ajax_list', compact('notifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function markAsRead($id)
    {
        $success = $this->notificationService->markAsRead($id, auth()->id());

        if ($success) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(auth()->user());
        return back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
    }
}
