<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    // Conclusão de atividade mudou.
    [
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => '\availability_classmetrics\observer::bump',
        'includefile' => '/availability/condition/classmetrics/classes/observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    // Inscrições (criar/atualizar/remover).
    [
        'eventname'   => '\core\event\user_enrolment_created',
        'callback'    => '\availability_classmetrics\observer::bump',
        'includefile' => '/availability/condition/classmetrics/classes/observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\core\event\user_enrolment_updated',
        'callback'    => '\availability_classmetrics\observer::bump',
        'includefile' => '/availability/condition/classmetrics/classes/observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => '\availability_classmetrics\observer::bump',
        'includefile' => '/availability/condition/classmetrics/classes/observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    // Mudanças de grupos.
    [
        'eventname'   => '\core\event\group_member_added',
        'callback'    => '\availability_classmetrics\observer::bump',
        'includefile' => '/availability/condition/classmetrics/classes/observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
    [
        'eventname'   => '\core\event\group_member_removed',
        'callback'    => '\availability_classmetrics\observer::bump',
        'includefile' => '/availability/condition/classmetrics/classes/observer.php',
        'internal'    => false,
        'priority'    => 9999,
    ],
];
