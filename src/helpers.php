<?php

declare(strict_types=1);

use Priyanshu\LaravelJev\JevManager;
use Priyanshu\LaravelJev\Support\BatchAnalysis;

if (! function_exists('jev')) {
    /**
     * Get the Jev manager or begin a batch analysis against an input string.
     */
    function jev(?string $input = null): JevManager|BatchAnalysis
    {
        /** @var JevManager $manager */
        $manager = app('jev');

        if ($input !== null) {
            return $manager->analyze($input);
        }

        return $manager;
    }
}
