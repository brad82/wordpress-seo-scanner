<?php

namespace BMDigital\SeoScanner\Collectors;

use DOMXPath;

class GenericTagsCollector
{
	public static function collect(DOMXPath $xpath): array
	{
		$collection = [];

		$tags = array(
			'title' => './/head/title',
			'description' => './/head/description',
		);

		foreach ($tags as $tag => $query) {
			$elems = $xpath->query($query);
			if ($elems->count() > 0) {
				$collection[$tag] = $elems->item(0)->textContent;
			}
		}

		return $collection;
	}
}
