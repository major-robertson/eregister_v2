<?php

/*
 * Arizona: Arizona Department of Revenue, Form 5000A. Researched 2026-10-01
 * from azdor.gov, azleg.gov, aztaxes.gov, mtc.gov. Generated once from the
 * EREG-8 resale research; edit this file directly from now on.
 */

return [
    'state' => 'AZ',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Arizona Department of Revenue',
        'short' => 'ADOR',
        'url' => 'https://azdor.gov/',
    ],
    'resale_page_url' => 'https://azdor.gov/forms/tpt-forms/arizona-resale-certificate',
    'form' => [
        'number' => '5000A',
        'title' => 'Arizona Resale Certificate',
        'pdf_url' => 'https://azdor.gov/sites/default/files/2023-03/FORMS_TPT_5000A_10316_0.pdf',
        'prescribed' => true,
        'revision' => 'ADOR 10316 (8/25)',
        'notes' => 'Prescribed by ADOR under A.R.S. § 42-5022; published 08/20/2025. Completed by the purchaser and kept by the vendor; not sent to ADOR. For exemptions other than resale, Arizona uses Form 5000 (Transaction Privilege Tax Exemption Certificate).',
    ],
    'issuer_model' => 'purchaser_completed',
    'registration' => [
        'name' => 'Arizona Transaction Privilege Tax (TPT) License',
        'number_name' => 'TPT license number',
        'format' => '8 digits',
        'verify_url' => 'https://www.aztaxes.gov/Home/LicenseVerification',
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) lists AZ with note 3: only for purchases of tangible personal property for resale in the ordinary course of business, and only if it contains the purchaser\'s name, address, signature and Arizona TPT (or other state sales tax) license number, as required by A.R.S. § 42-5022.',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'Arizona is not a Streamlined Sales Tax member state; ADOR\'s TPP 17-1 lists only ADOR certificates (Form 5000A for resale). No ADOR page found accepting the SST certificate.',
        ],
        'out_of_state_registration' => [
            'value' => true,
            'cite' => 'Form 5000A (ADOR 10316 (8/25)): \'Wholesalers must have a Transaction Privilege Tax ("TPT") or other state\'s Sales Tax License to purchase tangible personal property for resale.\' TPP 17-1 footnote 3: \'The Department accepts valid out-of-state sales tax license numbers for resale purposes only.\'',
            'notes' => 'Valid for resale purchases only, not for other exemptions.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Form 5000A box B (\'Period From ... Through\'); ADOR TPP 17-1 (blanket period must be specified; open-ended periods may not be accepted in good faith)',
        ],
    ],
    'expiration' => [
        'label' => 'Period set on the form',
        'summary' => 'Form 5000A is either a single-transaction certificate or covers a specific period the purchaser enters. Open-ended periods may not be accepted in good faith. ADOR encourages a period of no more than 12 months (a calendar year), because TPT licenses are renewed every year by January 1. A blanket certificate is treated as accepted in good faith for up to 48 months if the vendor keeps printouts from ADOR\'s license verification showing the TPT license was valid for each calendar year covered.',
        'cite' => 'Form 5000A box B; ADOR TPP 17-1 (Procedure for Use of Exemption Certificates); A.R.S. § 42-5005; https://azdor.gov/transaction-privilege-tax/tpt-license/renewing-tpt-license',
    ],
    'good_faith' => [
        'summary' => 'The seller bears the burden of proving a sale was not at retail unless it takes a certificate signed by the purchaser, with the purchaser\'s name, address and valid license number, stating the property is bought for resale in the ordinary course of business. The certificate must be obtained at the time of sale; incomplete certificates are not accepted in good faith. A seller with reason to believe the certificate is inaccurate, incomplete or not applicable cannot accept it in good faith. A seller that accepts it in good faith is relieved of the burden of proof, and ADOR may require the purchaser to prove the exemption.',
        'cite' => 'A.R.S. §§ 42-5022, 42-5009; Form 5000A section F; ADOR TPP 17-1',
    ],
    'misuse_penalty' => [
        'summary' => 'If the buyer uses or consumes the goods other than by reselling them, the buyer owes Arizona use tax. A purchaser who cannot support the certificate becomes liable for the tax the vendor would have paid, plus penalty and interest. Form 5000A warns that willful misuse subjects the purchaser to felony criminal penalties under A.R.S. § 42-1127, which makes knowingly presenting a false or fraudulent document a class 5 felony.',
        'cite' => 'Form 5000A section F; A.R.S. § 42-1127(B); ADOR TPP 17-1',
    ],
    'facts' => [
        [
            'text' => 'Arizona\'s transaction privilege tax is a tax on the vendor for the privilege of doing business, not a sales tax on the buyer. The vendor may pass it on but remains liable to the state, which is why the vendor must document resale sales with Form 5000A.',
            'source_url' => 'https://azdor.gov/sites/default/files/2023-03/PROCEDURES_TPT_2017_TPP17-1.pdf',
        ],
        [
            'text' => 'Form 5000A was revised in August 2025 (ADOR 10316 (8/25), published 08/20/2025). It now has optional business email and telephone fields and a section E for listed buyers that do not need a TPT license, such as the U.S. government, unlicensed Arizona school districts and certain 501(c) organizations (which must attach their IRS determination letter).',
            'source_url' => 'https://azdor.gov/forms/tpt-forms/arizona-resale-certificate',
        ],
        [
            'text' => 'In a drop shipment, ADOR treats the sale to the reseller and the reseller\'s sale to its customer as two separate transactions. The primary seller is exempt from TPT or use tax collection if the reseller gives a valid resale certificate, regardless of the reseller\'s Arizona nexus.',
            'source_url' => 'https://azdor.gov/sites/default/files/2023-03/RULINGS_TPT_1995_tpr95-13.pdf',
        ],
        [
            'text' => 'TPT licenses are valid for one calendar year, January 1 through December 31, and must be renewed by January 1. There is no state renewal fee; city fees range from $0 to $50.',
            'source_url' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/renewing-tpt-license',
        ],
        [
            'text' => 'A vendor can verify an eight-digit TPT license number on AZTaxes.gov. Keeping printouts of the verification for each year lets a blanket certificate be relied on for up to 48 months.',
            'source_url' => 'https://www.aztaxes.gov/Home/LicenseVerification',
        ],
    ],
    'state_notes' => 'In Arizona, use Form 5000A, the Arizona Resale Certificate, from the Arizona Department of Revenue (ADOR). You fill it out and give it to your supplier. Do not send it to ADOR. Enter your business name and address and your Arizona transaction privilege tax (TPT) license number. ADOR also accepts a valid sales tax license number from another state for resale purchases. Describe the precise nature of your business and the property you are buying. Then sign, print your name, and add your title and the date. The certificate can cover one purchase or a period with set start and end dates. ADOR encourages a period of 12 months or less, because TPT licenses are renewed every January 1. A supplier can rely on a certificate for up to 48 months if it checks that your license was valid each year. Your supplier must get a complete certificate at the time of the sale. If you later use the items yourself instead of reselling them, you owe Arizona use tax. If you cannot support the certificate, you owe the tax plus penalty and interest. The form warns that willful misuse can be a felony under Arizona law.',
    'sources' => [
        [
            'title' => 'ADOR: Arizona Resale Certificate (Form 5000A)',
            'url' => 'https://azdor.gov/forms/tpt-forms/arizona-resale-certificate',
        ],
        [
            'title' => 'Form 5000A PDF, ADOR 10316 (8/25)',
            'url' => 'https://azdor.gov/sites/default/files/2023-03/FORMS_TPT_5000A_10316_0.pdf',
        ],
        [
            'title' => 'ADOR TPP 17-1: Procedure for Use of Exemption Certificates',
            'url' => 'https://azdor.gov/sites/default/files/2023-03/PROCEDURES_TPT_2017_TPP17-1.pdf',
        ],
        [
            'title' => 'A.R.S. § 42-5022',
            'url' => 'https://www.azleg.gov/ars/42/05022.htm',
        ],
        [
            'title' => 'A.R.S. § 42-1127',
            'url' => 'https://www.azleg.gov/ars/42/01127.htm',
        ],
        [
            'title' => 'ADOR TPR 95-13: Third-party drop shipments',
            'url' => 'https://azdor.gov/sites/default/files/2023-03/RULINGS_TPT_1995_tpr95-13.pdf',
        ],
        [
            'title' => 'ADOR: Renewing a TPT License',
            'url' => 'https://azdor.gov/transaction-privilege-tax/tpt-license/renewing-tpt-license',
        ],
        [
            'title' => 'AZTaxes License Verification',
            'url' => 'https://www.aztaxes.gov/Home/LicenseVerification',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
