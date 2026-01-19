<?php

use App\Domains\Forms\Engine\SensitiveDataProtector;
use Illuminate\Encryption\Encrypter;

describe('SensitiveDataProtector', function () {
    it('encrypts and decrypts simple sensitive fields', function () {
        $encrypter = new Encrypter(str_repeat('a', 32), 'AES-256-CBC');
        $protector = new SensitiveDataProtector($encrypter);

        $baseDefinition = [
            'core_steps' => [
                'step1' => [
                    'fields' => [
                        'ssn' => ['type' => 'text', 'sensitive' => true],
                        'name' => ['type' => 'text'],
                    ],
                ],
            ],
        ];

        $data = ['ssn' => '1234', 'name' => 'John'];

        $encrypted = $protector->encryptCoreData($data, $baseDefinition);

        expect($encrypted['ssn'])->not->toBe('1234');
        expect($encrypted['name'])->toBe('John');

        $decrypted = $protector->decryptCoreData($encrypted, $baseDefinition);

        expect($decrypted['ssn'])->toBe('1234');
        expect($decrypted['name'])->toBe('John');
    });

    it('encrypts and decrypts repeater sensitive fields', function () {
        $encrypter = new Encrypter(str_repeat('a', 32), 'AES-256-CBC');
        $protector = new SensitiveDataProtector($encrypter);

        $baseDefinition = [
            'core_steps' => [
                'step1' => [
                    'fields' => [
                        'people' => [
                            'type' => 'repeater',
                            'schema' => [
                                'name' => ['type' => 'text'],
                                'ssn_last4' => ['type' => 'text', 'sensitive' => true],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $data = [
            'people' => [
                ['_id' => 'a', 'name' => 'John', 'ssn_last4' => '1234'],
                ['_id' => 'b', 'name' => 'Jane', 'ssn_last4' => '5678'],
            ],
        ];

        $encrypted = $protector->encryptCoreData($data, $baseDefinition);

        expect($encrypted['people'][0]['ssn_last4'])->not->toBe('1234');
        expect($encrypted['people'][1]['ssn_last4'])->not->toBe('5678');
        expect($encrypted['people'][0]['name'])->toBe('John');

        $decrypted = $protector->decryptCoreData($encrypted, $baseDefinition);

        expect($decrypted['people'][0]['ssn_last4'])->toBe('1234');
        expect($decrypted['people'][1]['ssn_last4'])->toBe('5678');
    });

    it('encrypts and decrypts person_state_extra fields', function () {
        $encrypter = new Encrypter(str_repeat('a', 32), 'AES-256-CBC');
        $protector = new SensitiveDataProtector($encrypter);

        $stateDefinition = [
            'state_steps' => [
                'step1' => [
                    'fields' => [
                        'responsible_people_extra' => [
                            'type' => 'person_state_extra',
                            'schema' => [
                                'driver_license' => ['type' => 'text', 'sensitive' => true],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $data = [
            'responsible_people_extra' => [
                'uuid-1' => ['driver_license' => 'DL12345'],
                'uuid-2' => ['driver_license' => 'DL67890'],
            ],
        ];

        $encrypted = $protector->encryptStateData($data, $stateDefinition);

        expect($encrypted['responsible_people_extra']['uuid-1']['driver_license'])->not->toBe('DL12345');
        expect($encrypted['responsible_people_extra']['uuid-2']['driver_license'])->not->toBe('DL67890');

        $decrypted = $protector->decryptStateData($encrypted, $stateDefinition);

        expect($decrypted['responsible_people_extra']['uuid-1']['driver_license'])->toBe('DL12345');
        expect($decrypted['responsible_people_extra']['uuid-2']['driver_license'])->toBe('DL67890');
    });

    it('handles empty strings gracefully', function () {
        $encrypter = new Encrypter(str_repeat('a', 32), 'AES-256-CBC');
        $protector = new SensitiveDataProtector($encrypter);

        $baseDefinition = [
            'core_steps' => [
                'step1' => [
                    'fields' => [
                        'ssn' => ['type' => 'text', 'sensitive' => true],
                    ],
                ],
            ],
        ];

        $data = ['ssn' => ''];

        $encrypted = $protector->encryptCoreData($data, $baseDefinition);

        expect($encrypted['ssn'])->toBe('');
    });
});
