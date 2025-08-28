<?php

namespace availability_classmetrics;

defined("MOODLE_INTERNAL") || die();

use core_availability\frontend as core_frontend;

class frontend extends core_frontend {

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
        // Get course activities with completion enabled.
        $activities = [];
        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->get_cms() as $cminfo) {
            if ($cminfo->uservisible && $cminfo->completion != COMPLETION_DISABLED) {
                $activities[] = [
                    'id' => $cminfo->id,
                    'name' => $cminfo->name,
                    'modname' => $cminfo->modname
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
        
        // Return data as a numeric array to match JavaScript expectations.
        return [
            $activities,
            $groups
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


