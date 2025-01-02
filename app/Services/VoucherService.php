<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SimpleXMLElement;

class VoucherService
{
    public function getVouchers(int $page, int $paginate, array $filters): LengthAwarePaginator
    {
        $query = Voucher::with(['lines', 'user'])
            ->where('user_id', auth()->id()); // Solo obtener los comprobantes del usuario autenticado

        // Aplicar los filtros opcionales si se han proporcionado
        if ($filters['serie']) {
            $query->where('series', $filters['serie']);
        }

        if ($filters['number']) {
            $query->where('number', $filters['number']);
        }

        if ($filters['voucher_type']) {
            $query->where('voucher_type', $filters['voucher_type']);
        }

        if ($filters['currency']) {
            $query->where('currency', $filters['currency']);
        }

        if ($filters['start_date'] && $filters['end_date']) {
            // Filtrar por rango de fechas
            $query->whereBetween('created_at', [
                $filters['start_date'],
                $filters['end_date']
            ]);
        }

        return $query->paginate(perPage: $paginate, page: $page);
    }


    /**
     * Almacenar los vouchers a partir de los contenidos XML
     *
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        $failedVouchers = [];

        foreach ($xmlContents as $xmlContent) {
            $vouchers[] = $this->storeVoucherFromXmlContent($xmlContent, $user);
        }

        // Despachar el evento después de la creación
        VouchersCreated::dispatch($vouchers, $user, $failedVouchers);

        return $vouchers;
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $xml = new SimpleXMLElement($xmlContent);

        // Extraer información existente con validación
        $issuerName = isset($xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0])
            ? (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0]
            : null;

        $issuerDocumentType = isset($xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0])
            ? (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0]
            : null;

        $issuerDocumentNumber = isset($xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0])
            ? (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0]
            : null;

        $receiverName = isset($xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0])
            ? (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0]
            : null;

        $receiverDocumentType = isset($xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0])
            ? (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0]
            : null;

        $receiverDocumentNumber = isset($xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0])
            ? (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0]
            : null;

        $totalAmount = isset($xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0])
            ? (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0]
            : null;

        // Extraer nueva información con validación
        $series = isset($xml->xpath('//cbc:ID')[0]) ? (string) $xml->xpath('//cbc:ID')[0] : null;
        $number = isset($xml->xpath('//cbc:ID')[1]) ? (string) $xml->xpath('//cbc:ID')[1] : null;
        $voucherType = isset($xml->xpath('//cbc:InvoiceTyoeCode')[0]) ? (string) $xml->xpath('//cbc:InvoiceTyoeCode')[0] : null;
        $currency = isset($xml->xpath('//cbc:LegalMonetaryTotal/cbc:PayableAmount/@currencyID')[0])
            ? (string) $xml->xpath('//cbc:LegalMonetaryTotal/cbc:PayableAmount/@currencyID')[0]
            : null;

        // Crear el comprobante
        $voucher = new Voucher([
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'series' => $series,
            'number' => $number,
            'voucher_type' => $voucherType,
            'currency' => $currency,
            'xml_content' => $xmlContent,
            'user_id' => $user->id,
        ]);

        $voucher->save();

        // Guardar las líneas del comprobante
        foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
            $name = isset($invoiceLine->xpath('cac:Item/cbc:Description')[0])
                ? (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0]
                : null;
            $quantity = isset($invoiceLine->xpath('cbc:InvoicedQuantity')[0])
                ? (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0]
                : 0;
            $unitPrice = isset($invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0])
                ? (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0]
                : 0.0;

            $voucherLine = new VoucherLine([
                'name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'voucher_id' => $voucher->id,
            ]);

            $voucherLine->save();
        }

        return $voucher;
    }


    public function getTotalAmountsByCurrency(User $user): array
    {
        // Inicializamos los totales
        $totalSoles = 0;
        $totalDolares = 0;

        // Obtener todos los vouchers registrados por el usuario
        $vouchers = Voucher::where('user_id', $user->id)->get();

        // Iterar sobre los vouchers y acumular los montos según la moneda
        foreach ($vouchers as $voucher) {
            if ($voucher->currency === 'PEN') {
                $totalSoles += $voucher->total_amount;
            } elseif ($voucher->currency === 'USD') {
                $totalDolares += $voucher->total_amount;
            }
        }

        // Devolver los totales acumulados por moneda
        return [
            'PEN' => $totalSoles,
            'USD' => $totalDolares
        ];
    }

    public function deleteVoucherById(string $id, User $user): bool
    {
        $voucher = Voucher::where('id', $id)->where('user_id', $user->id)->first();

        if ($voucher) {
            $voucher->delete();
            return true;
        }
        return false;
    }
}
