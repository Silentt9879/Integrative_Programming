<?php
// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'invoice_number',
        'invoice_data',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'issued_date'
    ];

    protected $casts = [
        'invoice_data' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_date' => 'datetime'
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // Auto-generate invoice number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = 'INV' . date('Y') . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
            
            if (!$invoice->issued_date) {
                $invoice->issued_date = now();
            }
        });
    }
}

