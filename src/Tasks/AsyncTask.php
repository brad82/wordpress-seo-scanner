<?php

namespace BMDigital\SeoScanner\Tasks;

use ArgumentCountError;
use Exception;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;

abstract class AsyncTask
{
	/**
	 * Default task priorty
	 * @var int
	 */
	protected static int $priority = 10;

	/**
	 * Toggles strict argument type checking
	 * @internal
	 * @var bool
	 */
	protected static bool $_strict = true;

	/**
	 * @var string
	 */
	protected static string $task_name = '';

	/**
	 * @return void 
	 * @throws Exception 
	 */
	public static function register(): void
	{
		add_action(static::get_task_name(), array(static::class, '_boot_task'), static::$priority, 1);
	}

	/**
	 * @return string 
	 * @throws Exception 
	 */
	protected static function get_task_name(): string
	{
		return !empty(static::$task_name) ? static::$task_name : throw new Exception('Task name not set');
	}

	/**
	 * @param array $args 
	 * @return void 
	 * @throws ArgumentCountError 
	 * @throws InvalidArgumentException 
	 * @throws ReflectionException 
	 */
	public static function _boot_task(array $args): void
	{
		$instance = new static();

		$reflection = new ReflectionMethod($instance, 'run');

		$params = array();
		foreach ($reflection->getParameters() as $index => $param) {
			$name = $param->getName();

			if (!array_key_exists($name, $args)) {
				if (!$param->isOptional()) {
					throw new ArgumentCountError($name . ' is required and was not found in action parameters');
				}

				$params[$index] = null;
				continue;
			}

			$coerced_value = $args[$name];
			$cast_result = settype($coerced_value, $param->getType());
			if (!$cast_result && static::$_strict) {
				throw new InvalidArgumentException(
					sprintf(
						'%1$s could not be coerced into expected type (got %2$s, needed %3$s)',
						$name,
						gettype($args[$name]),
						$param->getType()
					)
				);
			}

			$params[$index] = $coerced_value;
		}

		$reflection->invokeArgs($instance, $params);
	}
}
