<?php

/*
 * Iowa: Iowa Department of Revenue, Form 31-014. Researched 2026-10-01 from
 * revenue.iowa.gov, legis.iowa.gov. Generated once from the EREG-8 resale
 * research; edit this file directly from now on.
 */

return [
    'state' => 'IA',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Iowa Department of Revenue',
        'short' => 'IDR',
        'url' => 'https://revenue.iowa.gov/',
    ],
    'resale_page_url' => 'https://revenue.iowa.gov/forms/common-forms',
    'form' => [
        'number' => '31-014',
        'title' => 'Iowa Sales/Use/Excise Tax Exemption Certificate',
        'pdf_url' => 'https://revenue.iowa.gov/media/2265/download?inline=',
        'prescribed' => true,
        'revision' => '31-014a / 31-014d (09/08/2025)',
        'notes' => 'A multi-purpose exemption certificate with a Resale box. Under 701 IAC 209.1(3), the MTC Uniform Sales & Use Tax Resale Certificate and the Streamlined Sales Tax Certificate of Exemption are accepted alternatives.',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Iowa sales and use tax permit',
        'number_name' => 'Iowa sales and use tax permit number',
        'format' => null,
        'verify_url' => 'https://data.iowa.gov/catalog/lookerartifact/33',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => '701 IAC 209.1(3): https://www.legis.iowa.gov/docs/iac/rule/701.209.1.pdf',
        ],
        'sst' => [
            'value' => true,
            'cite' => '701 IAC 209.1(3); Iowa Code ch. 423 is the Streamlined Sales and Use Tax Act: https://www.legis.iowa.gov/docs/iac/rule/701.209.1.pdf',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => '701 IAC 209.1(1)(a)(4) and 209.1(2)(d); Form 31-014 instructions',
            'notes' => 'The identification number may be an Iowa permit, another state\'s sales tax ID, or an FEIN. A reseller that makes no taxable retail sales in Iowa need not hold an Iowa permit; retailers that hold an Iowa permit must enter it.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Iowa Code 423.51(3)(d); 701 IAC 209.1(4)',
        ],
    ],
    'expiration' => [
        'label' => 'No fixed expiration',
        'summary' => 'A blanket certificate stays in effect until the purchaser cancels it or until 12 months pass with no purchases between the same purchaser and seller. While there is a recurring business relationship (no more than 12 months between sales), the Department may not ask the seller to renew it.',
        'cite' => 'Iowa Code 423.51(3)(d); 701 IAC 209.1(4); Form 31-014 instructions',
    ],
    'good_faith' => [
        'summary' => 'The seller is relieved of liability if it obtains a fully completed certificate (or captures its data elements) at the time of sale or within 90 days after. If not, it may, within 120 days of a Department request, obtain a fully completed certificate taken in good faith or prove the sale was not taxable. Relief does not apply to a seller who fraudulently fails to collect tax or solicits purchasers to claim exemptions unlawfully.',
        'cite' => 'Iowa Code 423.51(2)-(3); Iowa Code 423.45(4)(d); 701 IAC 209.1(2)',
    ],
    'misuse_penalty' => [
        'summary' => 'The certificate is signed under penalty of perjury or false certificate. If goods bought tax-free are used or disposed of in a nonexempt manner, the purchaser alone owes the tax and must remit it to the Department, with the penalty and interest provisions of Iowa Code 423.40 and related sections applying to the purchaser.',
        'cite' => 'Iowa Code 423.45(4)(b) and (e); Form 31-014 signature declaration',
    ],
    'facts' => [
        [
            'text' => 'A person who sells but makes no taxable retail sales does not need an Iowa permit, but must still give the supplier a resale certificate to buy for resale.',
            'source_url' => 'https://www.legis.iowa.gov/docs/iac/rule/701.209.1.pdf',
        ],
        [
            'text' => 'Form 31-014 was revised 09/08/2025. It covers resale and other exemptions (processing, farm machinery, manufacturing machinery, qualifying software and digital services for commercial enterprises, and more).',
            'source_url' => 'https://revenue.iowa.gov/media/2265/download?inline=',
        ],
        [
            'text' => 'Rule 701-209.1 was rewritten effective August 28, 2024 (ARC 8151C) and now names the MTC and SST certificates as accepted alternatives to the Department\'s form.',
            'source_url' => 'https://www.legis.iowa.gov/docs/iac/rule/701.209.1.pdf',
        ],
        [
            'text' => 'Since July 1, 2022, Iowa uses a single sales and use tax permit and return; separate retailer\'s use and consumer\'s use permits were merged.',
            'source_url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-and-use-tax-permit-return-filing-and-payment-changes',
        ],
        [
            'text' => 'A signature is required only when a paper certificate is used; electronic exemption claims capture the same data elements.',
            'source_url' => 'https://www.legis.iowa.gov/docs/code/423.51.pdf',
        ],
    ],
    'state_notes' => 'In Iowa, buyers use Form 31-014, the Iowa Sales/Use/Excise Tax Exemption Certificate, from the Iowa Department of Revenue. The current version is dated 09/08/2025. Iowa also accepts the MTC Uniform Sales & Use Tax Resale Certificate and the Streamlined Sales Tax certificate. On Form 31-014, enter your legal name and address and the seller\'s. Check whether the certificate is for one purchase or is a blanket certificate. Mark that you are a retailer or wholesaler and check Resale. If you hold an Iowa sales and use tax permit, enter the number. A reseller that makes no taxable sales in Iowa does not need an Iowa permit and may give another state\'s tax ID. The purchaser or an authorized person signs and dates the form. A blanket certificate lasts until you cancel it or until 12 months pass with no purchases from that seller. The seller keeps the certificate. Do not send it to the Department. The seller must have the completed certificate within 90 days of the sale to be protected. You sign under penalty of perjury. If you use items bought tax-free in a taxable way, you alone owe the tax and must pay it directly to the Department, along with any penalty and interest.',
    'sources' => [
        [
            'title' => 'Form 31-014 (09/08/2025)',
            'url' => 'https://revenue.iowa.gov/media/2265/download?inline=',
        ],
        [
            'title' => 'Iowa DOR Common Forms',
            'url' => 'https://revenue.iowa.gov/forms/common-forms',
        ],
        [
            'title' => '701 IAC 209.1 Exemption certificates',
            'url' => 'https://www.legis.iowa.gov/docs/iac/rule/701.209.1.pdf',
        ],
        [
            'title' => 'Iowa Code 423.45',
            'url' => 'https://www.legis.iowa.gov/docs/code/423.45.pdf',
        ],
        [
            'title' => 'Iowa Code 423.51',
            'url' => 'https://www.legis.iowa.gov/docs/code/423.51.pdf',
        ],
        [
            'title' => 'Iowa DOR Permits FAQ',
            'url' => 'https://revenue.iowa.gov/permits-licensing/frequently-asked-questions/permits',
        ],
        [
            'title' => 'Sales and Use Tax Permit, Return Filing, and Payment Changes',
            'url' => 'https://revenue.iowa.gov/taxes/tax-guidance/sales-use-excise-tax/sales-and-use-tax-permit-return-filing-and-payment-changes',
        ],
    ],
];
