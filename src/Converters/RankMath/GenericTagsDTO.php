<?php

namespace BMDigital\SeoScanner\Converters\RankMath;

use BMDigital\SeoScanner\Converters\ConverterAbstract;
use BMDigital\SeoScanner\Converters\Persistors\UpdatesMeta;
use BMDigital\SeoScanner\Converters\Sanitizers\SanitizeText;

class GenericTagsDTO extends ConverterAbstract
{
	public static string $xpath = './/meta[starts-with(@property,"og")]';

	protected function method_from_tag(string $tag): string
	{
		return apply_filters('seo_scanner_generic_seo_converter_method', $tag, $tag);
	}

	#[UpdatesMeta('rank_math_title')]
	public function title(int $post_id, #[SanitizeText] string $content)
	{
		return $content;
	}

	#[UpdatesMeta('rank_math_title')]
	public function description(int $post_id, #[SanitizeText] string $content)
	{
		return $content;
	}
}
