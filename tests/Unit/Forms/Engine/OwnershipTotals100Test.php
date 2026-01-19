<?php

use App\Domains\Forms\Engine\Validation\Rules\OwnershipTotals100;

describe('OwnershipTotals100', function () {
    it('passes when ownership totals exactly 100', function () {
        $validator = new OwnershipTotals100;

        $data = [
            'responsible_people' => [
                ['_id' => 'a', 'full_name' => 'Person A', 'ownership_percent' => 50],
                ['_id' => 'b', 'full_name' => 'Person B', 'ownership_percent' => 50],
            ],
        ];

        $errors = $validator->validate($data, 'responsible_people');

        expect($errors)->toBeEmpty();
    });

    it('fails when ownership totals less than 100', function () {
        $validator = new OwnershipTotals100;

        $data = [
            'responsible_people' => [
                ['_id' => 'a', 'full_name' => 'Person A', 'ownership_percent' => 50],
                ['_id' => 'b', 'full_name' => 'Person B', 'ownership_percent' => 40],
            ],
        ];

        $errors = $validator->validate($data, 'responsible_people');

        expect($errors)->not->toBeEmpty();
        expect($errors['responsible_people'][0])->toContain('90');
    });

    it('fails when ownership totals more than 100', function () {
        $validator = new OwnershipTotals100;

        $data = [
            'responsible_people' => [
                ['_id' => 'a', 'full_name' => 'Person A', 'ownership_percent' => 60],
                ['_id' => 'b', 'full_name' => 'Person B', 'ownership_percent' => 60],
            ],
        ];

        $errors = $validator->validate($data, 'responsible_people');

        expect($errors)->not->toBeEmpty();
        expect($errors['responsible_people'][0])->toContain('120');
    });

    it('handles decimal percentages within tolerance', function () {
        $validator = new OwnershipTotals100;

        $data = [
            'responsible_people' => [
                ['_id' => 'a', 'ownership_percent' => 33.33],
                ['_id' => 'b', 'ownership_percent' => 33.33],
                ['_id' => 'c', 'ownership_percent' => 33.34],
            ],
        ];

        $errors = $validator->validate($data, 'responsible_people');

        expect($errors)->toBeEmpty();
    });

    it('handles empty array', function () {
        $validator = new OwnershipTotals100;

        $data = ['responsible_people' => []];
        $errors = $validator->validate($data, 'responsible_people');

        expect($errors)->toBeEmpty();
    });

    it('handles missing field', function () {
        $validator = new OwnershipTotals100;

        $errors = $validator->validate([], 'responsible_people');

        expect($errors)->toBeEmpty();
    });
});
