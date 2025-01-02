<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\User;

class StoreVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService) {}

    public function __invoke(Request $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            // Obtener los archivos XML del request
            $xmlFiles = $request->file('files');

            if (!$xmlFiles) {
                return response()->json([
                    'message' => 'No se han recibido archivos XML',
                ], 400);
            }

            if (!is_array($xmlFiles)) {
                $xmlFiles = [$xmlFiles];
            }

            $xmlContents = [];
            foreach ($xmlFiles as $xmlFile) {
                // Verificar si el archivo existe antes de obtener la ruta
                if ($xmlFile && $xmlFile->isValid()) {
                    $xmlContents[] = file_get_contents($xmlFile->getRealPath());
                } else {
                    return response()->json([
                        'message' => 'Archivo no válido o no encontrado',
                    ], 400);
                }
            }

            // Procesar los vouchers (comprobantes)
            $user = auth()->user();
            $vouchers = $this->voucherService->storeVouchersFromXmlContents($xmlContents, $user);

            return VoucherResource::collection($vouchers);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
