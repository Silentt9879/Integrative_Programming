<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BillingController extends Controller
{
    /**
     * Display user's billing dashboard with filtering
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query with filters
        $query = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('user_id', $user->id);
        
        // Filter by status
        if ($request->filled('status_filter')) {
            switch ($request->status_filter) {
                case 'paid':
                    $query->where('payment_status', 'paid')
                          ->where('damage_charges', 0)
                          ->where('late_fees', 0);
                    break;
                case 'pending':
                    $query->where(function($q) {
                        $q->whereIn('payment_status', ['pending', 'partial'])
                          ->orWhere('damage_charges', '>', 0)
                          ->orWhere('late_fees', '>', 0);
                    });
                    break;
                case 'has_additional':
                    $query->where(function($q) {
                        $q->where('damage_charges', '>', 0)
                          ->orWhere('late_fees', '>', 0);
                    });
                    break;
            }
        }
        
        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter by payment method (this would need a payment_method column or related table)
        // For now, we'll leave this as a placeholder
        
        $bookings = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Calculate summary
        $allBookings = Booking::where('user_id', $user->id)->get();
        
        $summary = [
            'total_paid' => $allBookings->where('payment_status', 'paid')
                ->filter(function($b) { 
                    return $b->damage_charges == 0 && $b->late_fees == 0; 
                })->sum('total_amount'),
            'pending_payment' => $allBookings->whereIn('payment_status', ['pending', 'partial'])
                ->sum('total_amount'),
            'additional_charges' => $allBookings->sum('damage_charges') + $allBookings->sum('late_fees'),
            'total_bookings' => $allBookings->count(),
            'active_bookings' => $allBookings->whereIn('status', ['confirmed', 'active'])->count(),
        ];
        
        // Get bookings with unpaid additional charges
        $outstandingBills = $allBookings->filter(function($booking) {
            // Check if booking has unpaid additional charges
            $hasUnpaidCharges = ($booking->damage_charges > 0 || $booking->late_fees > 0);
            
            // Check if it's a pending payment
            $isPending = in_array($booking->payment_status, ['pending', 'partial']);
            
            return $hasUnpaidCharges || $isPending;
        });
        
        // Count pending bills
        $summary['pending_bills'] = $outstandingBills->count();
        $summary['pending_amount'] = $outstandingBills->sum(function($b) {
            return $b->damage_charges + $b->late_fees;
        });
        
        // Get recent payments (bookings that are paid)
        $recentPayments = $allBookings->where('payment_status', 'paid')
            ->take(5);
        
        return view('billing.index', compact('bookings', 'summary', 'recentPayments', 'outstandingBills'));
    }
    
    /**
     * Show detailed billing for specific booking
     */
    public function show($bookingId)
    {
        $booking = Booking::with(['vehicle', 'vehicle.rentalRate', 'user'])
            ->where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Check if there are unpaid additional charges
        $hasUnpaidCharges = ($booking->damage_charges > 0 || $booking->late_fees > 0);
        
        // Calculate breakdown
        $subtotal = $booking->total_amount;
        $additionalCharges = $booking->damage_charges + $booking->late_fees;
        $grandTotal = $subtotal + $additionalCharges;
        
        $breakdown = [
            'base_rental' => $booking->total_amount,
            'deposit' => $booking->deposit_amount,
            'damage_charges' => $booking->damage_charges,
            'late_fees' => $booking->late_fees,
            'additional_total' => $additionalCharges,
            'subtotal' => $subtotal + $additionalCharges,
            'grand_total' => $grandTotal,
            'has_unpaid_charges' => $hasUnpaidCharges,
            'unpaid_amount' => $hasUnpaidCharges ? $additionalCharges : 0
        ];
        
        // Empty collections as placeholders
        $payments = collect([]);
        $invoice = null;
        
        return view('billing.show', compact('booking', 'payments', 'invoice', 'breakdown'));
    }
    
    /**
     * Process payment for additional charges
     */
    public function payAdditionalCharges(Request $request, $bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Check if there are additional charges to pay
        $additionalCharges = $booking->damage_charges + $booking->late_fees;
        
        if ($additionalCharges > 0) {
            // Store the charges temporarily for the payment process
            session([
                'additional_payment' => [
                    'booking_id' => $booking->id,
                    'amount' => $additionalCharges,
                    'damage_charges' => $booking->damage_charges,
                    'late_fees' => $booking->late_fees,
                    'type' => 'additional_charges'
                ]
            ]);
            
            // Redirect to payment gateway
            return redirect()->route('payment.additional-charges', $booking->id);
        }
        
        return back()->with('info', 'No additional charges to pay.');
    }
    
    /**
     * Get billing summary via AJAX
     */
    public function getSummary()
    {
        $user = Auth::user();
        
        $bookings = Booking::where('user_id', $user->id)->get();
        
        // Count bookings with unpaid additional charges
        $pendingBills = $bookings->filter(function($booking) {
            return ($booking->damage_charges > 0 || $booking->late_fees > 0) ||
                   in_array($booking->payment_status, ['pending', 'partial']);
        })->count();
        
        $summary = [
            'total_paid' => $bookings->where('payment_status', 'paid')
                ->filter(function($b) { 
                    return $b->damage_charges == 0 && $b->late_fees == 0; 
                })->sum('total_amount'),
            'pending_payment' => $bookings->whereIn('payment_status', ['pending', 'partial'])
                ->sum('total_amount'),
            'additional_charges' => $bookings->sum('damage_charges') + $bookings->sum('late_fees'),
            'pending_bills' => $pendingBills
        ];
        
        return response()->json($summary);
    }
    
    /**
     * Export billing history as PDF
     */
    public function exportBillingHistory(Request $request)
    {
        $user = Auth::user();
        
        $startDate = $request->input('start_date', Carbon::now()->subMonths(3)->startOfDay());
        $endDate = $request->input('end_date', Carbon::now()->endOfDay());
        
        $bookings = Booking::with(['vehicle', 'vehicle.rentalRate'])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $payments = collect([]);
        
        $totals = [
            'rental_charges' => $bookings->sum('total_amount'),
            'additional_charges' => $bookings->sum('damage_charges') + $bookings->sum('late_fees'),
            'total_paid' => $bookings->where('payment_status', 'paid')
                ->filter(function($b) { 
                    return $b->damage_charges == 0 && $b->late_fees == 0; 
                })->sum('total_amount'),
            'pending' => $bookings->filter(function($b) {
                return ($b->damage_charges > 0 || $b->late_fees > 0) ||
                       in_array($b->payment_status, ['pending', 'partial']);
            })->sum(function($b) {
                return $b->damage_charges + $b->late_fees;
            })
        ];
        
        $data = [
            'user' => $user,
            'bookings' => $bookings,
            'payments' => $payments,
            'totals' => $totals,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now(),
            'reportTitle' => 'Billing History Statement'
        ];
        
        $pdf = Pdf::loadView('reports.billing-statement', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $fileName = 'billing-statement-' . $user->id . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }
}