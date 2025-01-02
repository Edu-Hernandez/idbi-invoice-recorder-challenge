<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generar un XML simple como ejemplo
        $xmlContent = $this->generateXmlContent();

        return [
            'user_id' => User::factory(),
            'issuer_name' => $this->faker->company(),
            'issuer_document_type' => $this->faker->randomElement([1, 4, 6, 7]),
            'issuer_document_number' => $this->faker->randomNumber(8),
            'receiver_name' => $this->faker->name(),
            'receiver_document_type' => $this->faker->randomElement([1, 4, 6, 7]),
            'receiver_document_number' => $this->faker->randomNumber(8),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'xml_content' => $xmlContent,
        ];
    }

    /**
     * Genera un XML simple con datos de ejemplo.
     *
     * @return string
     */
    private function generateXmlContent(): string
    {
        // Crear un XML con datos de ejemplo
        $xml = new \SimpleXMLElement('<voucher></voucher>');
        $xml->addChild('issuer_name', $this->faker->company());
        $xml->addChild('issuer_document_type', $this->faker->randomElement([1, 4, 6, 7]));
        $xml->addChild('issuer_document_number', $this->faker->randomNumber(8));
        $xml->addChild('receiver_name', $this->faker->name());
        $xml->addChild('receiver_document_type', $this->faker->randomElement([1, 4, 6, 7]));
        $xml->addChild('receiver_document_number', $this->faker->randomNumber(8));
        $xml->addChild('total_amount', $this->faker->randomFloat(2, 10, 1000));

        return $xml->asXML();
    }
}
