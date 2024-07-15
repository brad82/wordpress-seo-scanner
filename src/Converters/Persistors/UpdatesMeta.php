<?php

namespace BMDigital\SeoScanner\Converters\Persistors;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class UpdatesMeta implements ValuePersistorContract
{
	public function __construct(
		private string $key,
		private bool $patch = true,
		private bool $skip_empty = true
	) {
	}

	public function save(int $post_id, mixed $value)
	{
		if (empty($value) && $this->skip_empty) {
			return;
		}

		update_post_meta($post_id, $this->key, $value, $this->patch);
	}
}
