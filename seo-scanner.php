<?php

/**
 * Plugin Name: SEO Scanner
 */

namespace BMDigital\SeoScanner;

use BMDigital\SeoScanner\Tasks\ProcessRemoteScan;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use function DI\factory;

include 'vendor/autoload.php';

$container = new \DI\Container([
	LoggerInterface::class => factory(function () {
		$logger = new Logger('seo-scanner');
		$logger->pushHandler(new StreamHandler(ABSPATH . '/wp-content/seo-scanner.log'));
		return $logger;
	})
]);

ProcessRemoteScan::register();

add_action('init', function () {
	if (!isset($_GET['bulk_sync'])) {
		return;
	}

	$posts = get_posts(
		array(
			'post_type' => $_GET['bulk_sync'],
			'posts_per_page' => -1
		)
	);

	$interval = 10; // seconds

	foreach ($posts as $index => $post) {
		$offset = time() + ($index * $interval);
		$full_path = substr(get_permalink($post), strlen(home_url('/')));

		as_schedule_single_action(
			time() + ($index * $interval),
			ProcessRemoteScan::$task_name,
			array(
				'post_id' => $post->ID,
				'url' => 'https://medcerts.com/' . $full_path,
			),
			'seo-scanner'
		);
	}
});
