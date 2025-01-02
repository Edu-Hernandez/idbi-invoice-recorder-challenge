<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Requests\Vouchers\GetVouchersRequest;
use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse; // Asegúrate de importar esto

class GetVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke(GetVouchersRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters(); // Obtener los filtros desde el request
        $vouchers = $this->voucherService->getVouchers(
            $request->query('page'),
            $request->query('paginate'),
            $filters
        );

        return VoucherResource::collection($vouchers);
    }


    public function getTotalAmounts(GetVouchersRequest $request): JsonResponse
    {
        $user = $request->user(); // Obtenemos el usuario autenticado
        $totalAmounts = $this->voucherService->getTotalAmountsByCurrency($user);

        return response()->json($totalAmounts, Response::HTTP_OK);
    }
}
