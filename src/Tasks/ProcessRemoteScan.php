<?php

namespace BMDigital\SeoScanner\Tasks;

use BMDigital\SeoScanner\Collectors\GenericTagsCollector;
use BMDigital\SeoScanner\Collectors\OpenGraphCollector;
use BMDigital\SeoScanner\Collectors\TwitterCollector;
use BMDigital\SeoScanner\Converters\RankMath\GenericTagsDTO;
use BMDigital\SeoScanner\Converters\RankMath\OpenGraphDTO;
use BMDigital\SeoScanner\Converters\RankMath\TwitterDTO;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use DOMXPath;
use Exception;
use Masterminds\HTML5;
use Psr\Log\LoggerInterface;

final class ProcessRemoteScan extends AsyncTask
{
	/**
	 *
	 * @var HTML5
	 */
	private HTML5 $parser;

	/**
	 * 
	 * @var int
	 */
	private int $post_id;

	/**
	 * 
	 * @var string
	 */
	public static string $task_name = 'seo_scanner_process_remote_scan';

	/**
	 * 
	 * @param Container $container 
	 * @return void 
	 */
	public function __construct(
		private Container $container,
		private LoggerInterface $log
	) {
		$this->parser = new HTML5([
			'encode_entities' => true,
			'disable_html_ns' => true
		]);
	}

	/**
	 * 
	 * @param int $post_id 
	 * @param string $url 
	 * @return void 
	 * @throws DependencyException 
	 * @throws NotFoundException 
	 */
	public function run(int $post_id, string $url)
	{
		$this->log->info('Fetching tags from remote', compact('url', 'post_id'));
		$response = wp_remote_get($url);
		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code !== 200) {
			$this->log->warning('Unexpected response code from remote', compact('url', 'post_id', 'response_code'));
			return;
		}

		$body = wp_remote_retrieve_body($response);
		if (empty($body)) {
			$this->log->warning('Remote returned an empty body ', compact('url', 'post_id'));
			return;
		}

		$this->post_id = $post_id;

		try {
			$this->extract_tags($body);
		} catch (Exception $e) {
			$this->log->warning('Could not extract tags from body', compact('url', 'post_id'));
			return;
		}

		update_post_meta($post_id, '_seo_scanner_last_scan', time());

		$this->log->info('Updated SEO for post', compact('url', 'post_id'));
	}

	/**
	 *
	 * @param mixed $html 
	 * @return void 
	 * @throws DependencyException 
	 * @throws NotFoundException 
	 */
	protected function extract_tags($html)
	{
		$document = $this->parser->loadHTML($html);
		$xpath = new DOMXPath($document);

		$map = apply_filters('seo_scanner_extractors', [
			OpenGraphCollector::class => OpenGraphDTO::class,
			TwitterCollector::class => TwitterDTO::class,
			GenericTagsCollector::class => GenericTagsDTO::class,
		]);

		foreach ($map as $collector => $dto) {
			$collector = $this->container->get($collector);
			$dto = $this->container->get($dto);

			$tags = $collector->collect($xpath);
			if (empty($tags)) {
				continue;
			}

			foreach ($tags as $tag => $value) {
				$dto->patch($this->post_id, $tag, $value);
			}
		}
	}
}
