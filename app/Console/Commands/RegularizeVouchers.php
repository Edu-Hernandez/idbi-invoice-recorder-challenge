<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use Illuminate\Console\Command;
use SimpleXMLElement;

class RegularizeVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:regularize-vouchers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $vouchers = Voucher::all();

        foreach ($vouchers as $voucher) {
            $xml = new SimpleXMLElement($voucher->xml_content);

            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cbc', $namespaces['cbc'] ?? ''); // Asegúrate de que 'cbc' está en el XML
            $xml->registerXPathNamespace('cac', $namespaces['cac'] ?? '');

            // Extraer nueva información
            $series = (string) $xml->xpath('//cbc:ID')[0]; // Ajusta el XPath según tu XML
            $number = (string) $xml->xpath('//cbc:ID')[1];
            $voucherType = (string) $xml->xpath('//cbc:InvoiceTyoeCode')[0];
            $currency = (string) $xml->xpath('//cbc:LegalMonetaryTotal/cbc:PayableAmount/@currencyID')[0];

            // Actualizar el comprobante
            $voucher->series = $series;
            $voucher->number = $number;
            $voucher->voucher_type = $voucherType;
            $voucher->currency = $currency;

            $voucher->save();
        }

        $this->info('Comprobantes regularizados exitosamente.');
    }
}
