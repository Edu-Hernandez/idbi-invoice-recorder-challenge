<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class GetVouchersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['required', 'int', 'gt:0'],
            'paginate' => ['required', 'int', 'gt:0'],
            'serie' => ['nullable', 'string', 'max:10'],
            'number' => ['nullable', 'string', 'max:10'],
            'voucher_type' => ['nullable', 'string', 'max:20'],
            'currency' => ['nullable', 'string', 'max:3'],
            'start_date' => ['required_with:end_date', 'date'],
            'end_date' => ['required_with:start_date', 'date'],
        ];
    }

    public function filters(): array
    {
        return [
            'serie' => $this->input('serie'),
            'number' => $this->input('number'),
            'voucher_type' => $this->input('voucher_type'),
            'currency' => $this->input('currency'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
        ];
    }
}
