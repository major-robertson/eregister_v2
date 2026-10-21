<?php

namespace App\Domains\Lien\Support;

/**
 * The lien products in the customer's words (EREG-143): what each one is
 * for, what it does and what it does not do, for the product chooser
 * ("What do you need to happen?") and the pages that carry a choice on
 * (sign-up, the project list and page).
 */
final class LienProducts
{
    /** The documents a customer can order, in the chooser's order. Keys are LienDocumentType slugs. */
    public const ORDERABLE = ['prelim_notice', 'noi', 'mechanics_lien', 'demand_letter', 'lien_release'];

    /** The chooser's card with no product behind it yet: a public job. */
    public const BOND_CLAIM = 'bond_claim';

    /**
     * Every card, keyed by product: the need in the customer's words, the
     * product's name, and one line each on what it does and does not do.
     *
     * @var array<string, array{need: string, name: string, does: string, does_not: string}>
     */
    public const CARDS = [
        'prelim_notice' => [
            'need' => 'Protect my right to lien later',
            'name' => 'Preliminary notice',
            'does' => 'Tells the owner you are working on the job. Many states require it before you can file a lien.',
            'does_not' => 'It does not put a lien on the property or ask for payment.',
        ],
        'noi' => [
            'need' => 'Warn them a lien is coming',
            'name' => 'Notice of intent to lien',
            'does' => 'A last written demand that says you will file a lien if you are not paid.',
            'does_not' => 'It does not put a lien on the property.',
        ],
        'mechanics_lien' => [
            'need' => 'Put a lien on the property',
            'name' => 'Mechanics lien',
            'does' => 'Records your claim for payment against the property with the county.',
            'does_not' => 'It does not collect the money for you or sue to enforce the lien.',
        ],
        'demand_letter' => [
            'need' => 'Ask for payment formally',
            'name' => 'Payment demand letter',
            'does' => 'A formal letter asking to be paid by a set date.',
            'does_not' => 'It does not protect your lien rights or meet a lien deadline.',
        ],
        'lien_release' => [
            'need' => 'Remove a lien I filed',
            'name' => 'Lien release',
            'does' => 'Records a release that clears your lien once you are paid.',
            'does_not' => 'It does not remove a lien someone else filed.',
        ],
        self::BOND_CLAIM => [
            'need' => 'Public or government job',
            'name' => 'Bond claim',
            'does' => 'Liens do not attach to public property. On a public job you usually claim against the contractor\'s payment bond instead.',
            'does_not' => 'We don\'t prepare bond claims yet.',
        ],
    ];

    /** Plural names for "We don't file {plural} in {State}." */
    private const PLURALS = [
        'prelim_notice' => 'preliminary notices',
        'noi' => 'notices of intent',
        'mechanics_lien' => 'mechanics liens',
        'demand_letter' => 'demand letters',
        'lien_release' => 'lien releases',
    ];

    public static function isOrderable(?string $product): bool
    {
        return in_array($product, self::ORDERABLE, true);
    }

    /** "Mechanics lien" */
    public static function name(string $product): string
    {
        return self::CARDS[$product]['name'] ?? ucfirst(str_replace('_', ' ', $product));
    }

    /** "mechanics liens" */
    public static function plural(string $product): string
    {
        return self::PLURALS[$product] ?? mb_strtolower(self::name($product)).'s';
    }

    /** The product as a sign-up intent slug (SignupIntent): "mechanics-lien". */
    public static function intentSlug(string $product): string
    {
        return str_replace('_', '-', $product);
    }
}
