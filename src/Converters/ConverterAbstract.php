<?php

namespace BMDigital\SeoScanner\Converters;

use BMDigital\SeoScanner\Converters\Persistors\ValuePersistorContract;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

abstract class ConverterAbstract
{
	public function __construct(
		protected LoggerInterface $log
	) {
		require_once(ABSPATH . 'wp-includes/pluggable.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
	}

	abstract protected function method_from_tag(string $tag): string;

	/**
	 * @param int $post_id 
	 * @param string $tag 
	 * @param string $value 
	 * @return void 
	 */
	public function patch(int $post_id, string $tag, string $value)
	{
		$method = $this->method_from_tag($tag);
		if (!method_exists($this, $method)) {
			$this->log->debug('No converter method registered for ' . $tag, compact('value'));
			return;
		}

		$reflector = new ReflectionMethod($this, $method);

		$value = $this->{$method}($post_id, $value);

		foreach ($reflector->getAttributes() as $methodAttribute) {
			$interfaces = class_implements($methodAttribute->getName());
			if (!in_array(ValuePersistorContract::class, $interfaces)) {
				continue;
			}

			$persistor = $methodAttribute->newInstance();
			$persistor->save($post_id, $value);
		}
	}
}
