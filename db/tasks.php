<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\availability_classmetrics\task\recalculate_conditions',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '2',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ]
];

