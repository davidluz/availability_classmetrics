<?php
defined('MOODLE_INTERNAL') || die();

$definitions = [
    // Cache leve por requisição para memoizar cálculos usando chaves simples.
    'metrics' => [
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 64,
    ],
];
