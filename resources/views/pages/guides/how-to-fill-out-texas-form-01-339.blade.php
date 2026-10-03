@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $faq = [
        ['q' => 'Where do I send Texas Form 01-339?', 'a' => 'You do not send it to the state. Give the completed certificate to the supplier you are buying from, and keep a copy. The form itself says to furnish it to the supplier and not to send it to the Comptroller of Public Accounts. The supplier keeps it on file for at least four years to show why it did not charge you tax.'],
        ['q' => 'Does a Texas resale certificate expire?', 'a' => 'There is no printed expiration date. A blanket resale certificate, which describes the general kinds of items you buy for resale, can be relied on by the seller until you revoke it in writing. One exception: if you gave a pending-application statement instead of a permit number, the certificate is valid for only 60 days, and you must give a new one with your permanent number.'],
        ['q' => 'Can I use Form 01-339 without a Texas sales tax permit?', 'a' => 'Usually not. A Texas buyer needs an 11-digit Texas sales and use tax permit number, or a statement that its application is pending. An out-of-state retailer can give its home-state registration number, and a retailer based in Mexico gives its RFC number plus a copy of its Mexico registration. A federal EIN or Social Security number does not qualify.'],
        ['q' => 'What is on the back of Form 01-339?', 'a' => 'The back is a separate form, the Texas Sales and Use Tax Exemption Certification. A buyer who qualifies for an exemption uses it to claim that exemption from tax, not to buy for resale. It needs no number, and it asks for the reason for the exemption. It cannot be used to buy, lease or rent a motor vehicle.'],
        ['q' => 'What happens if I give a false Texas resale certificate?', 'a' => 'Knowingly giving a false resale certificate is a crime under Texas Tax Code 151.707, graded by the tax avoided. It ranges from a Class C misdemeanor when the tax is under $20 to a second-degree felony at $20,000 or more. You also owe tax on anything you use instead of reselling, based on its price or fair market rental value.'],
    ];
    $sources = [
        ['name' => 'Texas Form 01-339, Sales and Use Tax Resale Certificate / Exemption Certification (Rev.4-13/8)', 'url' => 'https://comptroller.texas.gov/forms/01-339.pdf'],
        ['name' => 'Texas Comptroller, Resale Certificate FAQ', 'url' => 'https://comptroller.texas.gov/taxes/sales/faq/resale.php'],
        ['name' => '34 Tex. Admin. Code 3.285 (resale certificate; sales for resale)', 'url' => 'https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285'],
        ['name' => 'Tex. Tax Code 151.054 (gross receipts presumed subject to tax)', 'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm#151.054'],
        ['name' => 'Tex. Tax Code 151.707 (resale or exemption certificate; criminal penalty)', 'url' => 'https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm#151.707'],
        ['name' => 'Texas Comptroller, Sales and Use Tax Permit', 'url' => 'https://comptroller.texas.gov/taxes/permit/'],
    ];
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources">
    <p>
        Texas Form 01-339 is the Texas Sales and Use Tax Resale Certificate. You fill it out and give it to a supplier
        so you can buy items you will resell, rent or lease without paying sales tax at purchase. You need your
        11-digit Texas sales and use tax permit number, a description of what you are buying, a description of what
        your business sells, and your signature.
    </p>
    <p>
        The Texas Comptroller of Public Accounts publishes the form. The current version is Rev.4-13/8
        (<a href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank">Form 01-339</a>). The
        front is the resale certificate. The back is a different form, an exemption certification.
    </p>

    <h2>Who can use Form 01-339?</h2>
    <p>
        You can issue a resale certificate if you hold a Texas sales and use tax permit and you are buying a taxable
        item to resell, lease, rent, or transfer as part of a taxable service in the normal course of business
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
        You cannot use it for anything you know at purchase you will use or consume yourself.
    </p>
    <p>
        The items must be resold, rented or leased within the United States, its territories and possessions, or
        Mexico, as the form's certification states
        (<a href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank">Form 01-339</a>).
    </p>
    <p>Three other buyers can use it:</p>
    <ul>
        <li>
            <strong>A buyer with a pending application.</strong> Instead of a number, you can state that your permit
            application is pending and give the date you applied. The certificate is then good for only 60 days
            (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
        </li>
        <li>
            <strong>An out-of-state retailer.</strong> You can give the registration number your home state assigned, or a
            statement that you are not required to be registered there.
        </li>
        <li>
            <strong>A retailer based in Mexico.</strong> You give your Federal Taxpayers Registry (RFC) number and a copy
            of your Mexico registration form.
        </li>
    </ul>
    <p>
        An out-of-state retailer may still need its own Texas permit once its Texas sales pass the state's threshold;
        see <a href="/guides/when-do-you-need-a-sales-tax-permit">when you need a sales tax permit</a> and the
        <a href="/guides/economic-nexus-thresholds-by-state">economic nexus thresholds by state</a>. If you do not
        have a permit yet, start with <a href="/sales-tax-registration/texas">Texas sales tax registration</a>. The
        Comptroller says to allow 2 to 3 weeks to receive a permit when you apply online
        (<a href="https://comptroller.texas.gov/taxes/permit/" rel="noopener" target="_blank">Texas Comptroller</a>).
    </p>

    <h2>Fill in each field</h2>
    <x-guides.table id="form-01-339-fields-table">
        <thead>
            <tr>
                <th scope="col">Field on the form</th>
                <th scope="col">What to enter</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            <tr>
                <th scope="row">Name of purchaser, firm or agency</th>
                <td>Your name exactly as shown on your Texas permit</td>
            </tr>
            <tr>
                <th scope="row">Address, city, state, ZIP, phone</th>
                <td>Your business address and phone</td>
            </tr>
            <tr>
                <th scope="row">Texas Sales and Use Tax Permit Number</th>
                <td>Your 11-digit permit number</td>
            </tr>
            <tr>
                <th scope="row">Out-of-state registration or RFC number</th>
                <td>Only if you have no Texas permit and qualify as above</td>
            </tr>
            <tr>
                <th scope="row">Seller, street address, city, state, ZIP</th>
                <td>The supplier you are buying from</td>
            </tr>
            <tr>
                <th scope="row">Description of items to be purchased</th>
                <td>What you are buying for resale, or &quot;see attached order or invoice&quot;</td>
            </tr>
            <tr>
                <th scope="row">Description of business activity or items normally sold</th>
                <td>What your business sells</td>
            </tr>
            <tr>
                <th scope="row">Purchaser, title, date</th>
                <td>Signature of an authorized person, title, date</td>
            </tr>
        </tbody>
    </x-guides.table>
    <p>A few rules apply to these fields.</p>
    <p>
        <strong>Permit number.</strong> A Texas permit number has 11 digits and begins with a 1 or a 3. A federal
        employer identification number or a Social Security number is not acceptable evidence of resale
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
    </p>
    <p>
        <strong>Signature.</strong> The certificate must carry your signature, or an electronic signature the
        Comptroller authorizes, and the date.
    </p>

    <h2>How to describe the items and your business</h2>
    <p>These two descriptions are how the seller judges whether to accept the certificate, so make them specific.</p>
    <p>
        <strong>Items to be purchased.</strong> You can describe the items in general terms on the form, or itemize
        them on an order or invoice you attach
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
        For a one-time order, attach the order. For a blanket certificate covering future purchases, describe the
        general type of goods, such as &quot;plumbing fixtures and pipe for resale.&quot;
    </p>
    <p>
        <strong>Type of business.</strong> Say what you sell, such as &quot;retail hardware store&quot; or
        &quot;online apparel retailer.&quot; The two descriptions should fit together. The rule gives an example: a
        jewelry seller should know a resale certificate from a landscaping service is invalid, because a landscaping
        service does not resell jewelry. The Comptroller tells sellers to question a certificate when the buyer's
        business would not normally resell the item
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/resale.php" rel="noopener" target="_blank">Texas Comptroller resale FAQ</a>).
    </p>

    <h2>How is the exemption certification on the back different?</h2>
    <p>
        The back of Form 01-339 is the Texas Sales and Use Tax Exemption Certification. It is for buyers who are
        exempt from tax on a purchase, not buyers who will resell. It asks for the reason you claim the exemption
        instead of a business description
        (<a href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank">Form 01-339</a>).
    </p>
    <p>Three differences matter:</p>
    <ul>
        <li>
            The exemption side needs no number to be valid. The form adds that sales tax &quot;exemption numbers&quot; do
            not exist.
        </li>
        <li>It cannot be used to buy, lease or rent a motor vehicle.</li>
        <li>It carries its own criminal-offense warning for false use.</li>
    </ul>
    <p>Use only one side for a given purchase. If you are buying inventory to resell, complete the front.</p>

    <h2>How long the seller keeps it, and when it runs out</h2>
    <p>
        Do not send the completed form to the Comptroller. Give it to the supplier
        (<a href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank">Form 01-339</a>).
    </p>
    <p>
        If you are the seller, the certificate is your proof. Texas presumes every sale is taxable unless you accept a
        properly completed resale or exemption certificate
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm#151.054" rel="noopener" target="_blank">Tex. Tax Code 151.054</a>).
        The rule says to:
    </p>
    <ul>
        <li>get the certificate at or before the time of the sale;</li>
        <li>make sure every required field is filled in and legible;</li>
        <li>
            keep it at least four years from the date of the sale, and longer while tax for that period can still be
            assessed.
        </li>
    </ul>
    <p>
        During an audit, you get 90 days after the Comptroller's written request to produce certificates
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
    </p>
    <p>
        A blanket certificate stays good until the buyer revokes it in writing. Texas sets no expiration date, except
        the 60-day limit on a pending-application certificate.
    </p>
    <p>
        Texas also accepts the Border States Uniform Sale for Resale Certificate and the Multistate Tax Commission
        uniform certificate. It does not accept the Streamlined Sales Tax certificate as a resale certificate
        (<a href="https://www.law.cornell.edu/regulations/texas/34-Tex-Admin-Code-SS-3-285" rel="noopener" target="_blank">34 Tex. Admin. Code 3.285</a>).
    </p>

    <h2>Common mistakes and penalties</h2>
    <ul>
        <li>Putting an EIN in the permit number field.</li>
        <li>Leaving the item or business description blank. An incomplete certificate is disallowed.</li>
        <li>Using the certificate for supplies, tools or equipment your business will use.</li>
        <li>Giving a certificate after the sale instead of at or before it.</li>
    </ul>
    <p>
        If you use an item you bought without paying tax, other than holding it for sale, demonstration or display,
        you owe tax at the time of use. The form bases it on the purchase price or the fair market rental value for
        the time used
        (<a href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank">Form 01-339</a>). The
        Comptroller's FAQ says the same
        (<a href="https://comptroller.texas.gov/taxes/sales/faq/resale.php" rel="noopener" target="_blank">Texas Comptroller resale FAQ</a>).
    </p>
    <p>
        Knowingly giving a false certificate is a crime. The grade depends on the tax avoided
        (<a href="https://tcss.legis.texas.gov/resources/TX/htm/TX.151.htm#151.707" rel="noopener" target="_blank">Tex. Tax Code 151.707</a>):
    </p>
    <ul>
        <li>under $20: Class C misdemeanor;</li>
        <li>$20 to under $200: Class B misdemeanor;</li>
        <li>$200 to under $750: Class A misdemeanor;</li>
        <li>$750 to under $20,000: third-degree felony;</li>
        <li>$20,000 or more: second-degree felony.</li>
    </ul>
    <p>
        For how the permit and the certificate fit together, read
        <a href="/guides/sellers-permit-vs-resale-certificate">seller's permit vs resale certificate</a>. Our
        <a href="/resale-certificates/texas">Texas resale certificate page</a> summarizes the state's rules, and other
        states' forms are on the <a href="/resale-certificates">resale certificates</a> hub. eRegister can prepare and
        file your <a href="/sales-tax-registration/texas">Texas sales tax permit</a> application.
    </p>
    <p>This guide is general information, not tax advice. For a specific purchase, check with the Comptroller.</p>
</x-guides.article>
@endsection
