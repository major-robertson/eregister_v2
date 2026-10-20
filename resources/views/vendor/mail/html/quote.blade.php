@props(['text'])
{{-- Customer or admin text quoted as written: escaped, line breaks kept, and
     kept on one line so the surrounding Markdown pass leaves it alone (a "#"
     or an indented line must not turn into a heading or a code block). --}}
<table class="panel" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-content">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="panel-item">
<p>{!! str_replace(["\r\n", "\r", "\n"], '<br>', e($text)) !!}</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
