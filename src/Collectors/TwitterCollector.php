<?php

namespace BMDigital\SeoScanner\Collectors;

class TwitterCollector extends SimpleCollector
{
	public static $query = './head/meta[starts-with(@property,"og")]';
	public static $nameProperty = 'name';
	public static $valueProperty = 'content';
}
