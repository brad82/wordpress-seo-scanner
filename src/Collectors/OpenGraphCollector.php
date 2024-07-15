<?php

namespace BMDigital\SeoScanner\Collectors;

class OpenGraphCollector extends SimpleCollector
{
	public static $query = './head/meta[starts-with(@property,"og")]';
	public static $nameProperty = 'property';
	public static $valueProperty = 'content';
}
