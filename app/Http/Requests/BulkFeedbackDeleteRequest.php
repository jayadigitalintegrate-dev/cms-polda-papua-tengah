<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BulkFeedbackDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete-feedback') === true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'list', 'min:1', 'max:15'],
            'ids.*' => ['bail', 'required', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_int($value) && ! (is_string($value) && preg_match('/\A[0-9]+\z/', $value) === 1)) {
                    $fail('ID masukan harus berupa integer positif.');
                }
            }, 'integer', 'min:1', 'distinct', Rule::exists('feedback_submissions', 'id')],
            'page_scope' => ['required', 'string', 'max:4096'],
            'page' => ['required', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:100'],
        ];
    }

    public static function context(Request $request): array
    {
        return [
            'page' => max(1, (int) $request->input('page', 1)),
            'search' => trim((string) $request->input('search', '')),
            'category' => (string) $request->input('category', ''),
            'status' => (string) $request->input('status', ''),
        ];
    }

    private static function digest(array $context): string
    {
        // Search may contain PII: only its keyed digest enters the token.
        return hash_hmac('sha256', json_encode($context, JSON_THROW_ON_ERROR), self::key());
    }

    private static function key(): string
    {
        return app('encrypter')->getKey();
    }

    public static function issueScope(Request $request, array $ids): string
    {
        $payload = base64_encode(json_encode([
            'purpose' => 'feedback-hard-delete-v1',
            'actor' => (string) $request->user()->getAuthIdentifier(),
            'context' => self::digest(self::context($request)),
            'ids' => array_map('intval', $ids),
            'expires' => now()->addMinutes(10)->timestamp,
        ], JSON_THROW_ON_ERROR));

        return $payload.'.'.hash_hmac('sha256', $payload, self::key());
    }

    protected function passedValidation(): void
    {
        $parts = explode('.', $this->validated('page_scope'));
        $valid = count($parts) === 2
            && hash_equals(hash_hmac('sha256', $parts[0], self::key()), $parts[1]);
        $scope = $valid ? json_decode(base64_decode($parts[0], true) ?: '', true) : null;

        if (! is_array($scope)
            || ($scope['purpose'] ?? null) !== 'feedback-hard-delete-v1'
            || ($scope['actor'] ?? null) !== (string) $this->user()->getAuthIdentifier()
            || ! is_int($scope['expires'] ?? null)
            || $scope['expires'] <= now()->timestamp
            || ! hash_equals(self::digest(self::context($this)), (string) ($scope['context'] ?? ''))
            || ! is_array($scope['ids'] ?? null)
            || array_diff(array_map('intval', $this->validated('ids')), $scope['ids']) !== []) {
            throw ValidationException::withMessages([
                'page_scope' => 'Pilihan halaman tidak valid atau kedaluwarsa. Muat ulang daftar.',
            ]);
        }
    }
}
