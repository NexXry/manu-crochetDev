<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ENVGETTERExtension extends AbstractExtension
{
//    public function getFilters(): array
//    {
//        return [
//            // If your filter generates SAFE HTML, you should add a third
//            // parameter: ['is_safe' => ['html']]
//            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
//            new TwigFilter('filter_name', [$this, 'doSomething']),
//        ];
//    }

	public function getFunctions(): array
	{
		return [
			new TwigFunction('get_env', [$this, 'getEnvironmentVariable']),
		];
	}

	/**
	 * Return the value of the requested environment variable.
	 *
	 * @param String $varname
	 * @return String
	 */
	public function getEnvironmentVariable($varname)
	{
		return $_ENV[$varname];
	}
}
