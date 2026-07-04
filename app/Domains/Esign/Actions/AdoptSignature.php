<?php

namespace App\Domains\Esign\Actions;

use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Validates and stores a newly adopted site-wide signature (drawn PNG or
 * typed-name-rendered PNG) and marks it current. Callers layer their own
 * consent capture and audit events on top (resale pad → EsignConsent +
 * resale chain; esign review → the SignatureRequest event chain).
 */
class AdoptSignature
{
    public function execute(
        User $user,
        string $method,
        string $signatureImage,
        ?string $strokesJson = null,
        ?string $typedName = null,
        ?string $typedFont = null,
    ): UserSignature {
        if ($user->email_verified_at === null) {
            throw ValidationException::withMessages([
                'signature' => 'Verify your email address before adopting an electronic signature.',
            ]);
        }

        Validator::make(
            ['method' => $method, 'typed_name' => $typedName, 'typed_font' => $typedFont],
            [
                'method' => ['required', Rule::in([UserSignature::METHOD_DRAWN, UserSignature::METHOD_TYPED])],
                'typed_name' => ['required_if:method,'.UserSignature::METHOD_TYPED, 'nullable', 'string', 'max:255'],
                'typed_font' => [
                    'required_if:method,'.UserSignature::METHOD_TYPED,
                    'nullable',
                    Rule::in(array_keys(UserSignature::TYPED_FONTS)),
                ],
            ],
        )->validate();

        if (! preg_match('/^data:image\/png;base64,/', $signatureImage)) {
            throw ValidationException::withMessages([
                'signature' => 'The signature must be a PNG image.',
            ]);
        }

        $imageData = base64_decode(substr($signatureImage, strpos($signatureImage, ',') + 1), strict: true);

        if ($imageData === false || strlen($imageData) > 1048576) {
            throw ValidationException::withMessages([
                'signature' => 'The signature image is invalid or larger than 1MB.',
            ]);
        }

        $strokes = null;

        if ($method === UserSignature::METHOD_DRAWN && filled($strokesJson)) {
            $strokes = json_decode($strokesJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $strokes = null;
            }
        }

        $path = sprintf(
            '%s/signatures/user_%d_%s.png',
            config('resale_cert.storage_prefix'),
            $user->id,
            now()->format('YmdHis'),
        );

        Storage::disk(config('resale_cert.disk'))->put($path, $imageData);

        $signature = UserSignature::create([
            'user_id' => $user->id,
            'method' => $method,
            'image_path' => $path,
            'strokes_json' => $strokes,
            'typed_name' => $method === UserSignature::METHOD_TYPED ? $typedName : null,
            'typed_font' => $method === UserSignature::METHOD_TYPED ? $typedFont : null,
            'created_ip' => request()?->ip(),
            'user_agent' => (string) request()?->userAgent(),
            'is_current' => false,
            'agreed_to_terms' => true,
            'agreed_at' => now(),
        ]);

        $signature->markAsCurrent();

        return $signature;
    }

    /**
     * SHA-256 of the stored PNG bytes, for audit metadata.
     */
    public static function imageSha256(UserSignature $signature): ?string
    {
        $disk = Storage::disk(config('resale_cert.disk'));

        if (! $disk->exists($signature->image_path)) {
            return null;
        }

        return hash('sha256', (string) $disk->get($signature->image_path));
    }
}
