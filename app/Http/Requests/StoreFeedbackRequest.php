<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    /**
     * Endpoint publik: siapa pun boleh mengirim masukan.
     * Pembatasan dilakukan oleh throttle:public-feedback.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validasi masukan Kritik & Saran dari website.
     */
    public function rules(): array
    {
        return [
            'category' => [
                'required',
                'in:apresiasi,saran,kritik',
            ],

            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],

            'name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s]{8,20}$/',
            ],

            'consent' => [
                'required',
                'accepted',
            ],

            /*
             * Honeypot: kolom tersembunyi di website. Manusia tidak
             * mengisinya; bot yang mengisinya ditolak tanpa disimpan.
             */
            'website' => [
                'prohibited',
            ],
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk ditampilkan per kolom.
     */
    public function messages(): array
    {
        return [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',

            'rating.required' => 'Rating wajib dipilih.',
            'rating.integer' => 'Rating tidak valid.',
            'rating.between' => 'Rating harus bernilai 1 sampai 5.',

            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal :min karakter.',
            'message.max' => 'Pesan maksimal :max karakter.',

            'name.max' => 'Nama maksimal :max karakter.',

            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal :max karakter.',

            'phone.max' => 'Nomor HP maksimal :max karakter.',
            'phone.regex' => 'Nomor HP hanya boleh berisi angka, spasi, +, atau -, minimal 8 karakter.',

            'consent.required' => 'Persetujuan penggunaan data wajib dicentang.',
            'consent.accepted' => 'Persetujuan penggunaan data wajib dicentang.',

            'website.prohibited' => 'Masukan tidak dapat diproses.',
        ];
    }
}
