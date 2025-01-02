<?php

namespace App\Jobs;

use App\Mail\VouchersCreatedMail;
use App\Models\User;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessVoucherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $xmlContents;
    protected $user;

    public function __construct(array $xmlContents, User $user)
    {
        $this->xmlContents = $xmlContents;
        $this->user = $user;
    }

    public function handle(VoucherService $voucherService)
    {
        $vouchers = [];
        $failedVouchers = [];

        foreach ($this->xmlContents as $xmlContent) {
            try {
                // Intentar almacenar el comprobante
                $vouchers[] = $voucherService->storeVoucherFromXmlContent($xmlContent, $this->user);
            } catch (\Exception $e) {
                // Si ocurre un error, se registra en los fallidos
                $failedVouchers[] = ['xml' => $xmlContent, 'error' => $e->getMessage()];
            }
        }

        // Enviar correo con los resultados (comprobantes registrados y fallidos)
        SendVoucherReportMailJob::dispatch($this->user, $vouchers, $failedVouchers);
    }
}
