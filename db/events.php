<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    // Activity completion changed.
    [
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => '\availability_classmetrics\observer\completion_observer::completion_updated',
        'includefile' => '/availability/condition/classmetrics/classes/observer/completion_observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    // Enrolment events.
    [
        'eventname'   => '\core\event\user_enrolment_created',
        'callback'    => '\availability_classmetrics\observer\enrolment_observer::user_enrolment_created',
        'includefile' => '/availability/condition/classmetrics/classes/observer/enrolment_observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\core\event\user_enrolment_updated',
        'callback'    => '\availability_classmetrics\observer\enrolment_observer::user_enrolment_updated',
        'includefile' => '/availability/condition/classmetrics/classes/observer/enrolment_observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => '\availability_classmetrics\observer\enrolment_observer::user_enrolment_deleted',
        'includefile' => '/availability/condition/classmetrics/classes/observer/enrolment_observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    // Group membership changes.
    [
        'eventname'   => '\core\event\group_member_added',
        'callback'    => '\availability_classmetrics\observer\group_observer::group_member_added',
        'includefile' => '/availability/condition/classmetrics/classes/observer/group_observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\core\event\group_member_removed',
        'callback'    => '\availability_classmetrics\observer\group_observer::group_member_removed',
        'includefile' => '/availability/condition/classmetrics/classes/observer/group_observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
];
