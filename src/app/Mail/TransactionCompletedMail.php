<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $purchase;
    public $buyerName;
    public $itemName;

    /**
     * Create a new message instance.
     */
    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
        $this->buyerName = $purchase->user->name;
        $this->itemName = $purchase->item->name;
    }

    public function build()
    {
        return $this->subject('取引が完了しました - ' . $this->itemName)
            ->markdown('emails.transaction-completed');
    }
}
