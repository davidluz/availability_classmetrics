<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => '\availability_classmetrics\task\recalculate',
        'blocking'  => 0,
        'minute'    => 'R',       // Distribuído.
        'hour'      => '3',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
    ],
];
