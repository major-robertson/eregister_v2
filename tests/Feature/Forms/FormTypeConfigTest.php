<?php

use App\Domains\Forms\FormTypeConfig;

describe('FormTypeConfig', function () {
    it('returns config for valid form type', function () {
        $config = FormTypeConfig::get('sales_tax_permit');

        expect($config)->toBeArray();
        expect($config['name'])->toBe('Sales & Use Tax Permit');
        expect($config['billing_type'])->toBe('one_time_per_state');
        expect($config['state_mode'])->toBe('multi');
    });

    it('throws exception for invalid form type', function () {
        FormTypeConfig::get('nonexistent');
    })->throws(InvalidArgumentException::class, 'Unknown form type: nonexistent');

    it('returns all form types', function () {
        $all = FormTypeConfig::all();

        expect($all)->toBeArray();
        expect($all)->toHaveKey('sales_tax_permit');
        expect($all)->toHaveKey('llc');
    });

    it('returns definition directory for form type', function () {
        expect(FormTypeConfig::definitionDir('sales_tax_permit'))->toBe('SalesTaxPermit');
        expect(FormTypeConfig::definitionDir('llc'))->toBe('LLC');
    });

    it('checks if form type exists', function () {
        expect(FormTypeConfig::exists('sales_tax_permit'))->toBeTrue();
        expect(FormTypeConfig::exists('llc'))->toBeTrue();
        expect(FormTypeConfig::exists('nonexistent'))->toBeFalse();
    });

    it('returns billing type', function () {
        expect(FormTypeConfig::billingType('sales_tax_permit'))->toBe('one_time_per_state');
        expect(FormTypeConfig::billingType('llc'))->toBe('subscription');
    });

    it('returns state mode', function () {
        expect(FormTypeConfig::stateMode('sales_tax_permit'))->toBe('multi');
        expect(FormTypeConfig::stateMode('llc'))->toBe('single');
    });

    it('returns max states', function () {
        expect(FormTypeConfig::maxStates('sales_tax_permit'))->toBe(40);
        expect(FormTypeConfig::maxStates('llc'))->toBe(1);
    });

    it('checks if form type is subscription', function () {
        expect(FormTypeConfig::isSubscription('sales_tax_permit'))->toBeFalse();
        expect(FormTypeConfig::isSubscription('llc'))->toBeTrue();
    });

    it('returns subscription name for subscription form types', function () {
        expect(FormTypeConfig::subscriptionName('llc'))->toBe('llc');
        expect(FormTypeConfig::subscriptionName('sales_tax_permit'))->toBeNull();
    });

    it('returns form type keys', function () {
        $keys = FormTypeConfig::keys();

        expect($keys)->toContain('sales_tax_permit');
        expect($keys)->toContain('llc');
    });
});
