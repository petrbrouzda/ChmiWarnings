<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Latte\Runtime;

use Latte;
use Nette\PhpGenerator as Php;


/**
 * Generates blueprint of template class.
 * @internal
 */
class Blueprint
{
	use Latte\Strict;

	public function printClass(Template $template, string $name = null): void
	{
		if (!class_exists(Php\ClassType::class)) {
			throw new \LogicException('Nette PhpGenerator is required to print template, install package `nette/php-generator`.');
		}

		$name = $name ?: 'Template';
		$namespace = new Php\PhpNamespace(Php\Helpers::extractNamespace($name));
		$class = $namespace->addClass(Php\Helpers::extractShortName($name));

		$this->addProperties($class, $template->getParameters(), true);
		$functions = array_diff_key((array) $template->global->fn, (new Defaults)->getFunctions());
		$this->addFunctions($class, $functions);

		$end = $this->printCanvas();
		$this->printHeader('Native types');
		$this->printCode((string) $namespace);

		$this->addProperties($class, $template->getParameters(), false);

		$this->printHeader('phpDoc types');
		$this->printCode((string) $namespace);
		echo $end;
	}


	/**
	 * @param  mixed[]  $vars
	 */
	public function printVars(array $vars): void
	{
		if (!class_exists(Php\Type::class)) {
			throw new \LogicException('Nette PhpGenerator is required to print template, install package `nette/php-generator`.');
		}

		$res = '';
		foreach ($vars as $name => $value) {
			if (Latte\Helpers::startsWith($name, 'ʟ_')) {
				continue;
			}
			$type = Php\Type::getType($value) ?: 'mixed';
			$res .= "{varType $type $$name}\n";
		}

		$end = $this->printCanvas();
		$this->printHeader('varPrint');
		$this->printCode($res ?: 'No variables');
		echo $end;
	}


	/**
	 * @param  mixed[]  $props
	 */
	public function addProperties(Php\ClassType $class, array $props, bool $native = null): void
	{
		$printer = new Php\Printer;
		$native = $native ?? (PHP_VERSION_ID >= 70400);
		foreach ($props as $name => $value) {
			$type = Php\Type::getType($value);
			$prop = $class->addProperty($name);
			if ($native) {
				$prop->setType($type);
			} else {
				$doctype = $printer->printType($type, false, $class->getNamespace()) ?: 'mixed';
				$prop->setComment("@var $doctype");
			}
		}
	}


	/**
	 * @param  callable[]  $funcs
	 */
	public function addFunctions(Php\ClassType $class, array $funcs): void
	{
		$printer = new Php\Printer;
		foreach ($funcs as $name => $func) {
			$method = (new Php\Factory)->fromCallable($func);
			$type = $printer->printType($method->getReturnType(), $method->isReturnNullable(), $class->getNamespace()) ?: 'mixed';
			$class->addComment("@method $type $name" . $printer->printParameters($method, $class->getNamespace()));
		}
	}


	public function printCanvas(): string
	{
		echo "<div style='all:initial;position:fixed;overflow:auto;z-index:1000;left:0;right:0;top:0;bottom:0;color:black;background:white;padding:1em'>\n";
		return "</div>\n";
	}


	public function printHeader(string $string): void
	{
		echo "<h1 style='all:initial;display:block;font-size:2em;margin:1em 0'>", htmlspecialchars($string), "</h1>\n";
	}


	public function printCode(string $code): void
	{
		echo "<xmp style='margin:0;user-select:all'>", $code, "</xmp>\n";
	}
}
