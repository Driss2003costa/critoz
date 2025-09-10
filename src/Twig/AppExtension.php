<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('repeat', [$this, 'repeatString']),
        ];
    }

    public function repeatString(string $string, int $times): string
    {
        return str_repeat($string, $times);
    }
}