<?php
namespace availability_classmetrics;

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend {

    protected function allow_add($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        return true;
    }

    // IMPORTANTE: garante que o YUI do plugin seja carregado.
    protected function get_javascript_module() {
        return [
            'name' => 'moodle-availability_classmetrics-form',
            'fullpath' => '/availability/condition/classmetrics/yui/build/moodle-availability_classmetrics-form/moodle-availability_classmetrics-form.js',
            'requires' => ['base', 'node', 'event', 'moodle-core_availability-form'],
        ];
    }

    protected function get_javascript_init_params($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        $modinfo = get_fast_modinfo($course);
        $cms = [];
        foreach ($modinfo->cms as $cmid => $cmi) {
            if (!$cmi->completion) { continue; }
            $cms[] = ['id' => (int)$cmid, 'name' => (string)$cmi->name];
        }

        $groups = [];
        $groups[] = ['id' => 0, 'name' => get_string('nogroup', 'availability_classmetrics')];
        foreach (groups_get_all_groups($course->id) as $g) {
            $groups[] = [
                'id' => (int)$g->id,
                'name' => format_string($g->name, true, ['context' => \context_course::instance($course->id)]),
            ];
        }
        return [$cms, $groups];
    }

    protected function get_javascript_strings() {
        return [
            'title', 'description',
            'rule', 'rule_percent', 'rule_minstudents',
            'activities', 'aggregation', 'aggregation_all', 'aggregation_any',
            'percent', 'minstudents', 'group', 'nogroup',
            'error_noactivities', 'error_percent', 'error_minstudents',
        ];
    }
}
