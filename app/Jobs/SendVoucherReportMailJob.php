<?php

namespace App\Jobs;

use App\Mail\VouchersCreatedMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVoucherReportMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $vouchers;
    protected $failedVouchers;

    public function __construct(User $user, array $vouchers, array $failedVouchers)
    {
        $this->user = $user;
        $this->vouchers = $vouchers;
        $this->failedVouchers = $failedVouchers;
    }

    public function handle()
    {
        Mail::to($this->user->email)->send(new VouchersCreatedMail($this->vouchers, $this->user, $this->failedVouchers));
    }
}
