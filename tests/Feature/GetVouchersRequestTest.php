<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetVouchersRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_vouchers_request_validation()
    {
        // Validación con parámetros correctos
        $response = $this->getJson('/api/v1/vouchers', [
            'page' => 1,
            'paginate' => 10,
            'serie' => 'ABC123',
            'number' => '456',
            'voucher_type' => 'factura',
            'currency' => 'USD',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31'
        ]);

        $response->assertStatus(200);

        // Validación con parámetros incorrectos
        $response = $this->getJson('/api/v1/vouchers', [
            'page' => -1, // Valor inválido
            'paginate' => 10,
        ]);

        $response->assertStatus(422); // Esperamos un código 422 por error de validación
        $response->assertJsonValidationErrors(['page']); // Verificamos que la validación de 'page' falló
    }
}
