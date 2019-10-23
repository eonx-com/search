<?php


$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return PhpCsFixer\Config::create()
    ->setRules([
        // Enables all PSR2 rules
        '@PSR2' => true,
    ])
    ->setFinder($finder)
;
