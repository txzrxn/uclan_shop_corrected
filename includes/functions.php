<?php
function stock_label($status)
{
    if ($status === 'last-few') {
        return 'Last Few';
    }

    if ($status === 'out-of-stock') {
        return 'Out of Stock';
    }

    return 'In Stock';
}

function stock_badge_class($status)
{
    if ($status === 'last-few') {
        return 'badge-last';
    }

    if ($status === 'out-of-stock') {
        return 'badge-out';
    }

    return 'badge-good';
}

function format_price($amount)
{
    return '£' . number_format((float) $amount, 2);
}

function decode_order_payload($stored_value)
{
    $decoded = json_decode((string) $stored_value, true);

    if (is_array($decoded)) {
        return $decoded;
    }

    return [
        'items' => [],
        'total' => null,
        'legacy_value' => (string) $stored_value,
    ];
}

function text_length($value)
{
    return function_exists('mb_strlen') ? mb_strlen((string) $value) : strlen((string) $value);
}
