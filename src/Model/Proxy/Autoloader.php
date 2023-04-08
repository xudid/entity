<?php

namespace Xudid\Entity\Model\Proxy;

class Autoloader
{
	public static function register(string $cachePath)
	{
		spl_autoload_register(function ($class) use($cachePath) {
			$fileClassName = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			$fileName = $cachePath . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $fileClassName . '.php';
			if (file_exists($fileName)) {
				require_once $fileName;
			}
		});
	}
}
