<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\DI\Extensions;

use Nette;
use Nette\DI\Container;
use Nette\DI\DynamicParameter;
use Nette\PhpGenerator\Method;


/**
 * Parameters.
 */
final class ParametersExtension extends Nette\DI\CompilerExtension
{
	/** @var string[] */
	public $dynamicParams = [];

	/** @var string[][] */
	public $dynamicValidators = [];

	/** @var array */
	private $compilerConfig;


	public function __construct(array &$compilerConfig)
	{
		$this->compilerConfig = &$compilerConfig;
	}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$params = $this->config;
		foreach ($this->collectDynamicParams($params) as $key) {
			$params[$key] = new DynamicParameter((new Nette\PhpGenerator\Dumper)->format('$this->getParameter(?)', $key));
		}

		$builder->parameters = Nette\DI\Helpers::expand($params, $params, true);

		// expand all except 'services'
		$slice = array_diff_key($this->compilerConfig, ['services' => 1]);
		$slice = Nette\DI\Helpers::expand($slice, $builder->parameters);
		$this->compilerConfig = $slice + $this->compilerConfig;
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$builder = $this->getContainerBuilder();
		$dynamicParams = $this->collectDynamicParams($builder->parameters);

		$method = Method::from([Container::class, 'getStaticParameters'])
			->addBody('return ?;', [array_diff_key($builder->parameters, array_flip($dynamicParams))]);
		$class->addMember($method);

		if (!$dynamicParams) {
			return;
		}

		$resolver = new Nette\DI\Resolver($builder);
		$generator = new Nette\DI\PhpGenerator($builder);
		$method = Method::from([Container::class, 'getDynamicParameter']);
		$class->addMember($method);
		$method->addBody('switch (true) {');
		foreach ($dynamicParams as $key) {
			$value = Nette\DI\Helpers::expand($this->config[$key] ?? null, $builder->parameters);
			$value = $resolver->completeArguments(Nette\DI\Helpers::filterArguments([$value]));
			$method->addBody("\tcase \$key === ?: return ?;", [$key, $generator->convertArguments($value)[0]]);
		}
		$method->addBody("\tdefault: return parent::getDynamicParameter(\$key);\n};");

		$method = Method::from([Container::class, 'getParameters']);
		$class->addMember($method);
		$method->addBody('array_map(function ($key) { $this->getParameter($key); }, ?);', [array_values($dynamicParams)]);
		$method->addBody('return parent::getParameters();');

		foreach ($this->dynamicValidators as [$param, $expected]) {
			if (!$param instanceof Nette\DI\Definitions\Statement) {
				$this->initialization->addBody('Nette\Utils\Validators::assert(?, ?, ?);', [$param, $expected, 'dynamic parameter']);
			}
		}
	}


	private function collectDynamicParams(array $params): array
	{
		$keys = $this->dynamicParams;
		foreach ($params as $key => $value) {
			$tmp = [$value];
			array_walk_recursive($tmp, function ($val) use (&$keys, $key): void {
				if ($val instanceof DynamicParameter || $val instanceof Nette\DI\Definitions\Statement) {
					$keys[] = $key;
				}
			});
		}
		return array_unique($keys);
	}
}
