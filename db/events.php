<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\availability_classmetrics\observer\completion_observer::completion_updated',
    ],
    [
        'eventname' => '\core\event\user_enrolment_created',
        'callback' => '\availability_classmetrics\observer\enrolment_observer::user_enrolment_created',
    ],
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\availability_classmetrics\observer\enrolment_observer::user_enrolment_deleted',
    ],
    [
        'eventname' => '\core\event\user_enrolment_updated',
        'callback' => '\availability_classmetrics\observer\enrolment_observer::user_enrolment_updated',
    ],
    [
        'eventname' => '\core\event\group_member_added',
        'callback' => '\availability_classmetrics\observer\group_observer::group_member_added',
    ],
    [
        'eventname' => '\core\event\group_member_removed',
        'callback' => '\availability_classmetrics\observer\group_observer::group_member_removed',
    ],
];

