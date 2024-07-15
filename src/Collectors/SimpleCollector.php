<?php

namespace BMDigital\SeoScanner\Collectors;

use DOMXPath;

class SimpleCollector
{
	public static $query = './head/meta';
	public static $nameProperty = 'name';
	public static $valueProperty = 'content';

	public static function collect(DOMXPath $xpath): array
	{
		$elements = $xpath->query(static::$query);
		if ($elements->count() === 0) {
			return [];
		}

		$collection = [];
		foreach ($elements as $element) {
			$name = $element->getAttribute(static::$nameProperty);
			$value = $element->getAttribute(static::$valueProperty);

			$value = apply_filters('seo_scanner_tag_value', $value, $name);

			// @todo.. something should happen here if the meta tag already exists..
			//				 There are valid tags that may occure more than once such as 
			//				 og:see_also
			$collection[$name] = apply_filters('seo_scanner_tag_value_' . $name, $value);
		}

		return $collection;
	}
}
