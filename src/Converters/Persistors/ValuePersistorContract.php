<?php

namespace BMDigital\SeoScanner\Converters\Persistors;

interface ValuePersistorContract
{
	public function save(int $post_id, mixed $value);
}
