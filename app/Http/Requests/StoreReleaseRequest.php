<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreReleaseRequest extends FormRequest
{
    /**
     * TODO: authorize against a ReleasePolicy in the auth PR.
     * No users exist yet, so every guest may create.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ];
    }

    /**
     * @return array{title: string, body: string}
     */
    public function validated($key = null, $default = null): array
    {
        return parent::validated($key, $default);
    }
}
