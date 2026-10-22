{{-- One email the app sent, for an Activity history row (EREG-154). Needs $email. --}}
<flux:text class="text-sm font-medium text-text-primary">Email: {{ $email->subject ?: '(no subject)' }}</flux:text>
<flux:text class="text-xs text-gray-600">to {{ $email->recipientList() }}</flux:text>
@include('admin.partials.sent-email-status', ['email' => $email])
