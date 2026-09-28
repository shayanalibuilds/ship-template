<?php

declare(strict_types=1);

namespace App\Services\TwoFactor;

use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Renders text as an inline SVG QR code for authenticator apps.
 */
final class QrSvg
{
    /**
     * The SVG markup for the given content.
     */
    public function svg(string $content): string
    {
        $options = new QROptions([
            'eccLevel' => 1,
            'scale' => 5,
            'svgQuietZone' => 8,
            'outputInterface' => QRMarkupSVG::class,
        ]);

        $rendered = (new QRCode($options))->render($content);

        return is_string($rendered) ? $rendered : '';
    }
}
