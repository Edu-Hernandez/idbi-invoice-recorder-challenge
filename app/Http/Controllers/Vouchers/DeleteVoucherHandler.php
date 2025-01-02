<?php

namespace App\Http\Controllers\Vouchers;

use App\Services\VoucherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class DeleteVoucherHandler
{
    private VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function __invoke(string $id): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'No estás autenticado para realizar esta acción.',
            ], 401); // 401 Unauthorized
        }

        try {
            // Llamar al servicio para eliminar el comprobante
            $voucherDeleted = $this->voucherService->deleteVoucherById($id, $user);

            if ($voucherDeleted) {
                return response()->json([
                    'message' => 'Comprobante eliminado correctamente.',
                ], 200);
            }

            return response()->json([
                'message' => 'El comprobante no pertenece al usuario o no existe.',
            ], 403); // 403 Forbidden

        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Error al eliminar el comprobante: ' . $exception->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
}
