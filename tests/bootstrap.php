<?php
declare(strict_types=1);

require \dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @coversNothing
 */
if (\function_exists('xdebug_set_filter') === false) {
    return;
}

// Set xdebug filter to whitelist filter on the src directory for code coverage
/** @noinspection PhpUndefinedConstantInspection Constants are only defined if xdebug if loaded */
/** @noinspection PhpUndefinedFunctionInspection Function definition is checked above */
\xdebug_set_filter(
    \XDEBUG_FILTER_CODE_COVERAGE,
    \XDEBUG_PATH_WHITELIST,
    [\sprintf('%s/src', \dirname(__DIR__))]
);
