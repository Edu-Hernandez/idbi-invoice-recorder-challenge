<!DOCTYPE html>
<html>
<head>
    <title>Comprobantes Subidos</title>
</head>
<body>
    <h1>Estimado {{ $user->name }},</h1>
    <p>Hemos recibido tus comprobantes con los siguientes detalles:</p>

    <h2>Comprobantes registrados exitosamente</h2>
    @if(count($vouchers) > 0)
        @foreach ($vouchers as $voucher)
            <ul>
                <li>Nombre del Emisor: {{ $voucher->issuer_name }}</li>
                <li>Tipo de Documento del Emisor: {{ $voucher->issuer_document_type }}</li>
                <li>Número de Documento del Emisor: {{ $voucher->issuer_document_number }}</li>
                <li>Nombre del Receptor: {{ $voucher->receiver_name }}</li>
                <li>Tipo de Documento del Receptor: {{ $voucher->receiver_document_type }}</li>
                <li>Número de Documento del Receptor: {{ $voucher->receiver_document_number }}</li>
                <li>Monto Total: {{ $voucher->total_amount }}</li>
            </ul>
        @endforeach
    @else
        <p>No se registró ningún comprobante exitosamente.</p>
    @endif

    <h2>Comprobantes que no se pudieron registrar</h2>
    @if(count($failedVouchers) > 0)
        <ul>
            @foreach ($failedVouchers as $failed)
                <li>
                    <strong>XML:</strong> {{ $failed['xml'] }} <br>
                    <strong>Error:</strong> {{ $failed['error'] }}
                </li>
            @endforeach
        </ul>
    @else
        <p>No hubo fallos en el registro de comprobantes.</p>
    @endif

    <p>¡Gracias por usar nuestro servicio!</p>
</body>
</html>