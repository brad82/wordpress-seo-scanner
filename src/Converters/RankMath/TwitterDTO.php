<?php

namespace BMDigital\SeoScanner\Converters\RankMath;

use BMDigital\SeoScanner\Collectors\OpenGraphCollector;
use BMDigital\SeoScanner\Converters\ConverterAbstract;
use BMDigital\SeoScanner\Converters\Persistors\UpdatesMeta;
use BMDigital\SeoScanner\Converters\Sanitizers\SanitizeText;

class TwitterDTO extends ConverterAbstract
{
	public static string $collector = OpenGraphCollector::class;

	protected function method_from_tag(string $tag): string
	{
		$method = trim($tag);
		$method = str_replace("twitter:", "", $method);
		$method = str_replace(":", "__", $method);

		return apply_filters('seo_scanner_twitter_converter_method', $method, $tag);
	}

	public function image(int $post_id, string $content): void
	{
		$id = media_sideload_image($content, $post_id, 'Twitter Image', 'id');
		if (is_wp_error($id)) {
			$this->log->warning('Failed to sideload remote opengraph image. ' . $id->get_error_message(), array(
				'post_id' => $post_id,
				'remote_url' => $content,
			));
			return;
		}

		$url = wp_get_attachment_url($id);

		// Object cache seems to cause false hit when trying to update.. Deleting
		// first seemed to fix this on WPEngine.
		delete_post_meta($post_id, 'rank_math_twitter_image');
		delete_post_meta($post_id, 'rank_math_twitter_image_id');

		update_post_meta($post_id, 'rank_math_twitter_image', $url, true);
		update_post_meta($post_id, 'rank_math_twitter_image_id', $id, true);
	}

	#[UpdatesMeta('rank_math_twitter_title')]
	public function title(int $post_id, #[SanitizeText] string $content): string
	{
		return $content;
	}

	#[UpdatesMeta('rank_math_twitter_description')]
	public function description(int $post_id, #[SanitizeText] string $content): string
	{
		return $content;
	}

	#[UpdatesMeta('rank_math_twitter_card_type')]
	public function card(int $post_id, #[SanitizeText] string $content): string
	{
		$card_type = strtolower($content);
		$cards = array(
			'summary_large_image',
			'summary_card',
			'app',
			'player'
		);
		return in_array($card_type, $cards, true) ? $card_type : 'summary_large_image';
	}
}
