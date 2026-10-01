<?php

/*
 * Alaska: Alaska Remote Seller Sales Tax Commission, MTC uniform certificate
 * with your ARSSTC number, or your city or borough's certificate. Researched
 * 2026-10-01 from arsstc.org, mtc.gov. Generated once from the EREG-8 resale
 * research; edit this file directly from now on.
 */

return [
    'state' => 'AK',
    'researched_on' => '2026-10-01',
    'agency' => [
        'name' => 'Alaska Remote Seller Sales Tax Commission',
        'short' => 'ARSSTC',
        'url' => 'https://arsstc.org/',
    ],
    'resale_page_url' => 'https://arsstc.org/business-sellers/',
    'form' => [
        'number' => null,
        'title' => 'No state form. Remote resellers: ARSSTC Remote Reseller Sales Tax Exemption Certificate number entered on the MTC Uniform Sales & Use Tax Resale Certificate. Local businesses: the resale certificate issued by their city or borough.',
        'pdf_url' => 'https://arsstc.org/wp-content/uploads/2021/05/ARSSTC-Seller-Remote-Resale-Certificate-Application-fillable.pdf',
        'prescribed' => false,
        'revision' => null,
        'notes' => 'Alaska has no state sales tax. About 85 cities and boroughs belong to the ARSSTC, which administers sales tax on remote sales under the Alaska Remote Seller Sales Tax Code (amended July 2024, effective January 1, 2025). A remote seller with no physical presence in a member jurisdiction that buys for resale to buyers there applies to the ARSSTC for a Remote Reseller Certificate of Exemption (Code Section 190); the pdf_url is that application, returned to AMSTP@akml.org. ARSSTC guidance tells the buyer to complete the MTC Uniform Resale Certificate with the ARSSTC remote reseller certificate number. Businesses located in a taxing city or borough apply to that municipality for its own resale exemption certificate; ARSSTC\'s Exemption Certificate Directory lists each jurisdiction\'s forms and contacts.',
        'label' => 'MTC uniform certificate with your ARSSTC number, or your city or borough\'s certificate',
        'pdf_label' => 'ARSSTC remote reseller application',
    ],
    'issuer_model' => 'state_issued',
    'registration' => [
        'name' => 'ARSSTC remote seller registration (remote sellers) or local municipal sales tax registration (local businesses)',
        'number_name' => 'ARSSTC Remote Reseller Certificate of Exemption number, or the local jurisdiction\'s resale certificate number',
        'format' => null,
        'verify_url' => null,
    ],
    'accepts' => [
        'mtc' => [
            'value' => true,
            'cite' => 'MTC Uniform Resale Certificate (rev. 10/14/2022) note 1: valid for ARSSTC only if it carries the purchaser\'s name, address, signature and either the ARSSTC Remote Reseller Certificate of Exemption number or the local resale certificate number; https://arsstc.org/wp-content/uploads/2022/09/Resale-Exemption-Certificate-Guidance.pdf',
        ],
        'sst' => [
            'value' => false,
            'cite' => 'No ARSSTC or member-jurisdiction page found accepting the Streamlined Sales Tax certificate; Alaska is not an SST member. ARSSTC guidance names only local certificates, the ARSSTC remote reseller certificate and the MTC form.',
        ],
        'out_of_state_registration' => [
            'value' => false,
            'cite' => 'https://arsstc.org/wp-content/uploads/2022/09/Resale-Exemption-Certificate-Guidance.pdf (Scenario 5); Alaska Remote Seller Sales Tax Code § 190',
            'notes' => 'A non-Alaska business being charged Alaska local tax on deliveries into a taxing jurisdiction should register with the ARSSTC as a remote seller, regardless of the economic nexus threshold, and use its ARSSTC Remote Reseller certificate number. A home-state registration number alone is not named as acceptable.',
        ],
        'blanket' => [
            'value' => true,
            'cite' => 'Alaska Remote Seller Sales Tax Code § 190(B) (certificate valid for the calendar year); MTC certificate terms (valid for each order until cancelled)',
        ],
    ],
    'expiration' => [
        'label' => 'Expires December 31 each year',
        'summary' => 'The ARSSTC Remote Reseller Certificate of Exemption expires at the end of the calendar year in which it is issued. The buyer must renew it each year and may reapply in December for the next year. Local certificates follow each municipality\'s own rules.',
        'cite' => 'Alaska Remote Seller Sales Tax Code § 190(B) (amended July 2024); https://arsstc.org/faqs-for-sellers/',
    ],
    'good_faith' => [
        'summary' => 'No express good-faith safe harbor was found in the ARSSTC Code. The seller must request the certificate for every tax-exempt sale or hold it on file, keep documentation of every exempt sale for three years, and produce it to the ARSSTC on request. Exemption claims without documentation may be invalidated, and exemptions are narrowly construed against the claimant. The buyer is responsible for keeping its certificate current.',
        'cite' => 'https://arsstc.org/faqs-for-sellers/ ; https://arsstc.org/business-sellers/ ; Alaska Remote Seller Sales Tax Code §§ 010(C), 100',
    ],
    'misuse_penalty' => [
        'summary' => 'Misuse of an exemption card is a violation with a penalty of $50 per incident. Knowingly or negligently giving false information when applying for a certificate of exemption carries a $500 penalty. If a member jurisdiction\'s own code sets a different penalty, that penalty applies. A buyer that uses or consumes goods bought tax-free must pay the tax directly to the ARSSTC.',
        'cite' => 'Alaska Remote Seller Sales Tax Code § 250(A), (D), (G); ARSSTC Remote Reseller Sales Tax Exemption Certificate Application',
    ],
    'facts' => [
        [
            'text' => 'Alaska has no state sales tax; sales tax is levied by cities and boroughs. The ARSSTC member list (as of August 2026) has about 85 member jurisdictions. The Municipality of Anchorage has no retail sales tax and issues no exemption certificates.',
            'source_url' => 'https://arsstc.org/business-sellers/member-jurisdictions/',
        ],
        [
            'text' => 'An Alaska business in a non-taxing area that sells remotely across Alaska should register with the ARSSTC and get a remote reseller certificate to buy for resale. If it does not make remote sales, it does not qualify for the ARSSTC certificate and cannot use the MTC form for Alaska.',
            'source_url' => 'https://arsstc.org/wp-content/uploads/2022/09/Resale-Exemption-Certificate-Guidance.pdf',
        ],
        [
            'text' => 'Local resale exemption applications go directly to the city or borough, not to the ARSSTC. The ARSSTC Exemption Certificate Directory lists each jurisdiction\'s forms and contacts (for example Juneau, Bethel and Palmer issue resale exemptions).',
            'source_url' => 'https://arsstc.org/exemption-certificate-directory/',
        ],
        [
            'text' => 'The ARSSTC remote reseller application requires an active ARSSTC account number and certifies that goods or services bought tax-free will be resold or directly integrated into what the business sells.',
            'source_url' => 'https://arsstc.org/wp-content/uploads/2021/05/ARSSTC-Seller-Remote-Resale-Certificate-Application-fillable.pdf',
        ],
        [
            'text' => 'The Alaska Remote Seller Sales Tax Code was last amended in July 2024, effective January 1, 2025.',
            'source_url' => 'https://arsstc.org/about/code/',
        ],
    ],
    'state_notes' => 'Alaska has no state sales tax, but many cities and boroughs do. Which certificate you need depends on where your business is. If your business is located in a taxing city or borough, apply to that local government for its resale exemption certificate and give that certificate or number to your supplier. If you sell into Alaska without a physical presence in the taxing area, the Alaska Remote Seller Sales Tax Commission (ARSSTC) handles it. Register with the ARSSTC and apply for a Remote Reseller Certificate of Exemption. Then fill out the Multistate Tax Commission\'s Uniform Sales & Use Tax Resale Certificate and enter your ARSSTC number next to AK. The certificate must show your name, address and signature, and should describe what you are buying. The ARSSTC certificate expires on December 31 of the year it is issued, so you must renew it every year. Your supplier keeps the certificate on file to support the exempt sale. Under the ARSSTC Code, misuse of an exemption card carries a $50 penalty per incident, and false information on a certificate application carries a $500 penalty. If you use tax-free goods yourself, you must pay the tax to the ARSSTC.',
    'sources' => [
        [
            'title' => 'ARSSTC Business/Sellers: Resale/Exemption Certificates',
            'url' => 'https://arsstc.org/business-sellers/',
        ],
        [
            'title' => 'ARSSTC Resale Transaction Scenarios',
            'url' => 'https://arsstc.org/wp-content/uploads/2022/09/Resale-Exemption-Certificate-Guidance.pdf',
        ],
        [
            'title' => 'ARSSTC Remote Reseller Sales Tax Exemption Certificate Application',
            'url' => 'https://arsstc.org/wp-content/uploads/2021/05/ARSSTC-Seller-Remote-Resale-Certificate-Application-fillable.pdf',
        ],
        [
            'title' => 'Alaska Remote Seller Sales Tax Code (amended July 2024)',
            'url' => 'https://arsstc.org/wp-content/uploads/2024/09/Uniform-Code_2024-revisions_final_070824.pdf',
        ],
        [
            'title' => 'ARSSTC Code page',
            'url' => 'https://arsstc.org/about/code/',
        ],
        [
            'title' => 'ARSSTC FAQs for Sellers',
            'url' => 'https://arsstc.org/faqs-for-sellers/',
        ],
        [
            'title' => 'ARSSTC Exemption Certificate Directory',
            'url' => 'https://arsstc.org/exemption-certificate-directory/',
        ],
        [
            'title' => 'ARSSTC Member Jurisdictions',
            'url' => 'https://arsstc.org/business-sellers/member-jurisdictions/',
        ],
        [
            'title' => 'MTC Uniform Sales & Use Tax Resale Certificate (rev. 10/14/2022)',
            'url' => 'https://www.mtc.gov/wp-content/uploads/2023/01/Unif-Resale-Cert-revised-10-14-22.pdf',
        ],
    ],
];
