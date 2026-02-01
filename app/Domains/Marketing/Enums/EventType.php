<?php

namespace App\Domains\Marketing\Enums;

enum EventType: string
{
    case CtaClick = 'cta_click';
    case CallClick = 'call_click';
    case FormSubmit = 'form_submit';

    public function label(): string
    {
        return match ($this) {
            self::CtaClick => 'CTA Click',
            self::CallClick => 'Call Click',
            self::FormSubmit => 'Form Submit',
        };
    }
}
