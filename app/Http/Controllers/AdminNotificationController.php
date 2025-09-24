<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->is_admin) {
                abort(403, 'Admin access required');
            }
            return $next($request);
        });
    }

    /**
     * Display notifications in admin panel
     */
    public function index(Request $request)
    {
        $query = DB::table('admin_notifications')->orderBy('created_at', 'desc');

        // Filter by type if specified
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority if specified
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        $notifications = $query->paginate(20);

        // Get counts for dashboard
        $counts = [
            'total' => DB::table('admin_notifications')->count(),
            'unread' => DB::table('admin_notifications')->where('is_read', false)->count(),
            'high_priority' => DB::table('admin_notifications')->where('priority', 'high')->where('is_read', false)->count(),
            'action_required' => DB::table('admin_notifications')->where('action_required', true)->where('is_read', false)->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'counts'));
    }

    /**
     * Get notifications for header dropdown (AJAX)
     */
    public function getHeaderNotifications()
    {
        $notifications = DB::table('admin_notifications')
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $html = view('admin.notifications.dropdown', compact('notifications'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $notifications->count(),
            'unread_count' => DB::table('admin_notifications')->where('is_read', false)->count()
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $updated = DB::table('admin_notifications')
            ->where('id', $id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        if (request()->ajax()) {
            return response()->json([
                'success' => $updated > 0,
                'message' => $updated > 0 ? 'Notification marked as read' : 'Notification not found'
            ]);
        }

        return back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $updated = DB::table('admin_notifications')
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Marked {$updated} notifications as read"
            ]);
        }

        return back()->with('success', "Marked {$updated} notifications as read");
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        $deleted = DB::table('admin_notifications')->where('id', $id)->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => $deleted > 0,
                'message' => $deleted > 0 ? 'Notification deleted' : 'Notification not found'
            ]);
        }

        return back()->with('success', 'Notification deleted');
    }

    /**
     * Show specific notification details
     */
    public function show($id)
    {
        $notification = DB::table('admin_notifications')->where('id', $id)->first();

        if (!$notification) {
            abort(404, 'Notification not found');
        }

        // Mark as read when viewed
        DB::table('admin_notifications')
            ->where('id', $id)
            ->update(['is_read' => true, 'updated_at' => now()]);

        // Decode metadata
        $notification->metadata = json_decode($notification->metadata, true);

        // Get related data if available
        $relatedData = $this->getRelatedData($notification);

        return view('admin.notifications.show', compact('notification', 'relatedData'));
    }

    /**
     * Get related data for notification
     */
    private function getRelatedData($notification)
    {
        $relatedData = [];

        if ($notification->related_user_id) {
            $relatedData['user'] = DB::table('users')->where('id', $notification->related_user_id)->first();
        }

        if ($notification->related_booking_id) {
            $relatedData['booking'] = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
                ->where('bookings.id', $notification->related_booking_id)
                ->select('bookings.*', 'users.name as customer_name', 'vehicles.make', 'vehicles.model')
                ->first();
        }

        return $relatedData;
    }

    /**
     * Get notification statistics for dashboard widget
     */
    public function getStats()
    {
        $stats = [
            'total_today' => DB::table('admin_notifications')
                ->whereDate('created_at', today())
                ->count(),
            'unread_total' => DB::table('admin_notifications')
                ->where('is_read', false)
                ->count(),
            'high_priority_unread' => DB::table('admin_notifications')
                ->where('is_read', false)
                ->where('priority', 'high')
                ->count(),
            'action_required' => DB::table('admin_notifications')
                ->where('is_read', false)
                ->where('action_required', true)
                ->count(),
            'by_type' => DB::table('admin_notifications')
                ->where('is_read', false)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray()
        ];

        return response()->json($stats);
    }
}