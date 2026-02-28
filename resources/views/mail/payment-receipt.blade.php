<x-mail::message>
Hi {{ $userName }},

Thank you for your payment! Here's your receipt.

<x-mail::table>
| Item | Amount |
|:-----|-------:|
| {{ $itemDescription }} | {{ $amount }} |
</x-mail::table>

**Date:** {{ $paidAt }}<br>
**Receipt #:** {{ $paymentId }}

If you have any questions about this charge, please don't hesitate to reach out.

Thanks,<br>
Major<br>
eRegister
</x-mail::message>
