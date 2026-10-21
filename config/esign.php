<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Consent (ESIGN / UETA consumer disclosures)
    |--------------------------------------------------------------------------
    |
    | The consent screen text shown the first time a signer signs. The full
    | text is snapshotted verbatim onto each esign_consents row, so changing
    | any wording here should be accompanied by a new `version` (so prior
    | consents remain attributable to exactly what was shown). Consent is also
    | scoped — a signer who consented for `demand_letters` has NOT consented for
    | a later category of records unless the scope/version covers it.
    |
    */
    'consent' => [
        // Bumped 2026-07: support@ -> contact@ in the disclosures. Any wording
        // change requires a new version so prior consents stay attributable.
        'version' => '2026-07-v1',

        'heading' => 'Consent to Use Electronic Signatures and Records',

        'agreement' => 'I agree to conduct this transaction electronically and to use electronic signatures.',

        'checkbox' => 'I can access and retain electronic records, and I agree to use electronic signatures and electronic records for the documents described above.',

        'accept_button' => 'I Agree, Continue',

        // Consumer disclosures required by ESIGN for electronic records.
        'disclosures' => [
            'withdrawal' => 'You may withdraw your consent to use electronic records and signatures at any time before you sign by emailing contact@eregister.com. Withdrawing consent does not affect the legal validity of records or signatures provided electronically before the withdrawal.',
            'paper_copy' => 'You may request a paper copy of any document at no charge by emailing contact@eregister.com.',
            'fees' => 'There is no fee to receive these records electronically, to request a paper copy, or to withdraw your consent.',
            'contact_update' => 'To update the email address or other contact information we use to send you electronic records, email contact@eregister.com.',
            'hardware_software' => 'To access and retain these records you need: a device with internet access, a current web browser, a valid email account, and the ability to open and save PDF files (for example, a PDF reader). Because you can read this consent and the document on screen now, you can access the electronic form being used.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Signing screen
    |--------------------------------------------------------------------------
    |
    | The legally-meaningful strings shown on the review/sign screen. The intent
    | statement and button label are snapshotted onto the signature_request when
    | the signer signs, alongside the exact list of documents they signed.
    |
    */
    'signing' => [
        'typed_name_label' => 'Type your full legal name to adopt your electronic signature',
        'sign_button' => 'Sign All Demand Letters',
        'intent' => 'I have reviewed the demand letters listed below. By clicking Sign All Demand Letters, I intend to electronically sign each listed letter.',
        // How long the emailed signing link stays valid.
        'invitation_link_ttl_days' => 14,
        // Minimum gap between emails to a signer (the invitation or an admin
        // reminder), so a double-click or two admins can't stack sends.
        'reminder_cooldown_minutes' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-document-type signing policy
    |--------------------------------------------------------------------------
    |
    | Each signable document type declares its own rules. Demand letters are the
    | only one supported today; liens / prelim notices / resale certificates and
    | notarized documents plug in here later with different rules (e.g.
    | requires_notary => true routes to the future eNotarize path instead of
    | typed signatures). Read via App\Domains\Esign\DocumentSigningPolicy::for().
    |
    */
    'document_types' => [

        'demand_letter' => [
            'title' => 'Payment Demand Letters',
            'document_id_prefix' => 'DL',
            'supports_esign' => true,
            'requires_notary' => false,
            'signature_method' => 'typed_name',
            'requires_recipient_acknowledgment' => false,
            'allowed_signer_role' => 'filing_creator',
            'consent_scope' => 'demand_letters',
        ],

        'lien_waiver' => [
            'title' => 'Lien Waiver',
            'document_id_prefix' => 'LW',
            'supports_esign' => true,
            'requires_notary' => false,
            'signature_method' => 'typed_name',
            'requires_recipient_acknowledgment' => false,
            // provide: the business's own user signs; collect: the vendor
            // counterparty signs as a GUEST (no account, email-code identity).
            'allowed_signer_role' => 'waiver_signer',
            'consent_scope' => 'lien_waivers',
            'sign_button' => 'Sign Lien Waiver',
            'intent' => 'I have reviewed the lien waiver listed below. By clicking Sign Lien Waiver, I intend to electronically sign it and be bound by its terms.',
        ],

        // A lien document an admin uploaded as a draft (prelim notice, NOI,
        // mechanics lien, release), e-signed in the app (EREG-141). The stored
        // PDF is locked as it is; the signed copy appends a page with the
        // signature and the Certificate of Completion. Where it can be used
        // is set under uploaded_documents below.
        'uploaded_document' => [
            'title' => 'Lien document',
            'document_id_prefix' => 'LD',
            'supports_esign' => true,
            'requires_notary' => false,
            'signature_method' => 'typed_name',
            'requires_recipient_acknowledgment' => false,
            'allowed_signer_role' => 'filing_creator',
            'consent_scope' => 'lien_documents',
            'sign_button' => 'Sign document',
            'intent' => 'I have reviewed the document shown below. By clicking Sign document, I intend to electronically sign it.',
            // The signing emails say "review the document" (default: "letter").
            'document_noun' => 'document',
            // The review screen shows the locked PDF itself, not only its name.
            'inline_preview' => true,
        ],

        // Resale certificates use a reusable drawn signature (adopted once,
        // stamped on every generated certificate) rather than the one-shot
        // SignatureRequest ceremony, but consent is captured through the same
        // EsignConsent table with this scope.
        'resale_certificate' => [
            'title' => 'Resale Certificates',
            'document_id_prefix' => 'RC',
            'supports_esign' => true,
            'requires_notary' => false,
            'signature_method' => 'drawn_signature',
            'requires_recipient_acknowledgment' => false,
            'allowed_signer_role' => 'business_member',
            'consent_scope' => 'sales_tax_resale_certs',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Where uploaded lien documents can be e-signed (EREG-141)
    |--------------------------------------------------------------------------
    |
    | Notices (the lien document types listed in document_types) can be
    | e-signed in every state. Mechanics liens and releases only in the states
    | listed in lien_states (two-letter codes); everywhere else they take the
    | notarization path. Major fills the list. County exceptions come with
    | EREG-142. Demand letters are not affected by this.
    | Read via App\Domains\Lien\Esign\UploadedDocumentSignable::isAllowedFor().
    |
    */
    'uploaded_documents' => [
        'document_types' => ['prelim_notice', 'noi'],
        'lien_states' => [],
    ],

];
