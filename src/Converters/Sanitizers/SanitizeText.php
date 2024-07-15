<?php

namespace BMDigital\SeoScanner\Converters\Sanitizers;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SanitizeText
{
	public static function sanitize(string $value)
	{
		$sanitized = strip_tags($value);
		$sanitized = sanitize_text_field($sanitized);
		return apply_filters('seo_scanner_sanitize_text', $sanitized);
	}
}
