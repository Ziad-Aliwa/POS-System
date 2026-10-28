<?php

namespace App\Helpers;

class QRCodeHelper
{
    /**
     * Generate a QR Code Image URL or embedded SVG.
     */
    public static function url(string $data, int $size = 140): string
    {
        $encoded = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encoded}&margin=2";
    }
}
