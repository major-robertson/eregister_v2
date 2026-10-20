<x-mail::message>
Hi {{ $userName }},

To record your {{ $documentName }} we need the legal description or parcel number of the property.

@if($propertyAddress)
**Property:** {{ $propertyAddress }}
@endif

Here is how to find it:

@if($assessorUrl)
1. Look up the property on the [county assessor site]({{ $assessorUrl }}) and copy the {{ $parcelPhrase }}.
@else
1. Look up the property on the county assessor site and copy the {{ $parcelPhrase }}.
@endif
2. The legal description is on your deed or the owner's deed. The county recorder's office can give you a copy.
3. Enter it on your filing page.

**What a legal description looks like**

Lot and block: *Lot 12, Block 3, Sunset Hills Subdivision, as recorded in Plat Book 44, Page 7.*

Metes and bounds: *Beginning at the northeast corner of Section 14, Township 2 North, Range 3 West; thence south 200 feet; thence west 150 feet; thence north 200 feet; thence east 150 feet to the point of beginning.*

Copy it exactly as written. One wrong number can describe a different property.

<x-mail::button :url="$filingUrl">
Enter it now
</x-mail::button>

If you have any questions, just reply to this email.

Thanks,<br>
The eRegister Team
</x-mail::message>
