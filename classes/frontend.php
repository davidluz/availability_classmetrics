<?php

namespace availability_classmetrics;

defined("MOODLE_INTERNAL") || die();

use core_availability\frontend;

class frontend extends \core_availability\frontend {

    protected function get_javascript_strings() {
        return [
            "completion_percentage",
            "minimum_students",
            "select_activities",
            "select_group",
            "no_group",
            "error_percentage",
            "error_minimum",
            "error_activities"
        ];
    }

    protected function get_javascript_init_params(
        $course,
        \cm_info $cm = null,
        \section_info $section = null
    ) {
        global $DB;
        
        // Get course activities with completion enabled
        $activities = [];
        $coursemodules = $DB->get_records("course_modules", ["course" => $course->id]);
        foreach ($coursemodules as $cm) {
            $modinfo = get_module_info($cm->id);
            if ($modinfo && $modinfo->completion != COMPLETION_DISABLED) {
                $activities[] = [
                    'id' => $cm->id,
                    'name' => $modinfo->name,
                    'modname' => $modinfo->modname
                ];
            }
        }
        
        // Get course groups
        $groups = [];
        $coursegroups = groups_get_all_groups($course->id);
        foreach ($coursegroups as $group) {
            $groups[] = [
                'id' => $group->id,
                'name' => $group->name
            ];
        }
        
        return [
            'activities' => $activities,
            'groups' => $groups
        ];
    }

    protected function allow_add(
        $course,
        \cm_info $cm = null,
        \section_info $section = null
    ) {
        // Always allow adding this condition
        return true;
    }
}


