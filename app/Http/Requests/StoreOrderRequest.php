<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'tel' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment' => ['required', 'in:cod,wave'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'integer', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.variant' => ['nullable', 'string'],
            'items.*.image' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'firstname.required' => 'Le prénom est obligatoire.',
            'lastname.required' => 'Le nom est obligatoire.',
            'tel.required' => 'Le numéro WhatsApp est obligatoire.',
            'address.required' => 'L\'adresse de livraison est obligatoire.',
            'city.required' => 'La zone de livraison est obligatoire.',
            'payment.required' => 'Le mode de paiement est obligatoire.',
            'items.required' => 'Le panier est vide.',
            'items.min' => 'Le panier est vide.',
        ];
    }
}
