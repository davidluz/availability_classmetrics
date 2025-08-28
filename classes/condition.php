<?php

namespace availability_classmetrics;

defined("MOODLE_INTERNAL") || die();

use core_availability\condition as core_condition;
use core_availability\info;

class condition extends core_condition {

    protected $type;
    protected $percentage;
    protected $minimum;
    protected $activities;
    protected $groupid;

    public function __construct($structure) {
        $this->type = isset($structure->conditiontype) ? $structure->conditiontype : '';
        $this->percentage = isset($structure->percentage) ? (int)$structure->percentage : 0;
        $this->minimum = isset($structure->minimum) ? (int)$structure->minimum : 0;
        $this->activities = isset($structure->activities) ? $structure->activities : '';
        $this->groupid = isset($structure->groupid) ? (int)$structure->groupid : 0;
    }

    public function save() {
        $result = (object)[
            'type' => 'classmetrics',
            'conditiontype' => $this->type
        ];
        
        if ($this->type === 'completion') {
            $result->percentage = $this->percentage;
            $result->activities = $this->activities;
        } else if ($this->type === 'students') {
            $result->minimum = $this->minimum;
        }
        
        if ($this->groupid) {
            $result->groupid = $this->groupid;
        }
        
        return $result;
    }

    public function is_available($not, info $info, $grabthelot, $userid) {
        global $DB;
        
        $course = $info->get_course();
        $available = false;
        
        if ($this->type === 'completion') {
            $available = $this->check_completion_condition($course);
        } else if ($this->type === 'students') {
            $available = $this->check_students_condition($course);
        }
        
        if ($not) {
            $available = !$available;
        }
        
        return $available;
    }

    /**
     * Check if the completion percentage condition is met
     */
    private function check_completion_condition($course) {
        global $DB;
        
        // Get students in the course (optionally filtered by group)
        $students = $this->get_students_in_course($course);
        
        if (empty($students)) {
            return false;
        }
        
        $total_students = count($students);
        $completed_students = 0;
        
        // Check completion for each student
        foreach ($students as $student) {
            if ($this->student_completed_activities($student->id, $course->id)) {
                $completed_students++;
            }
        }
        
        $completion_percentage = ($completed_students / $total_students) * 100;
        
        return $completion_percentage >= $this->percentage;
    }

    /**
     * Check if the minimum students condition is met
     */
    private function check_students_condition($course) {
        $students = $this->get_students_in_course($course);
        return count($students) >= $this->minimum;
    }

    /**
     * Get students enrolled in the course, optionally filtered by group
     */
    private function get_students_in_course($course) {
        global $DB;
        
        $context = \context_course::instance($course->id);
        
        // Base query to get enrolled students
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {role_assignments} ra ON ra.userid = u.id
                JOIN {role} r ON r.id = ra.roleid
                WHERE e.courseid = :courseid
                AND ra.contextid = :contextid
                AND r.shortname = 'student'
                AND ue.status = 0
                AND u.deleted = 0
                AND u.suspended = 0";
        
        $params = [
            'courseid' => $course->id,
            'contextid' => $context->id
        ];
        
        // Add group filter if specified
        if ($this->groupid > 0) {
            $sql .= " AND u.id IN (
                        SELECT gm.userid 
                        FROM {groups_members} gm 
                        WHERE gm.groupid = :groupid
                      )";
            $params['groupid'] = $this->groupid;
        }
        
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Check if a student has completed the specified activities
     */
    private function student_completed_activities($userid, $courseid) {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');

        if (empty($this->activities)) {
            // No activities specified means condition is met.
            return true;
        }

        $course = get_course($courseid);
        $completion = new \completion_info($course);

        $activityids = array_filter(array_map('intval', explode(',', $this->activities)));

        foreach ($activityids as $cmid) {
            $cm = get_coursemodule_from_id(null, $cmid, $courseid, IGNORE_MISSING);
            if (!$cm) {
                // If the activity no longer exists treat as not completed.
                return false;
            }

            $data = $completion->get_data($cm, false, $userid);
            if (!$data || $data->completionstate < COMPLETION_COMPLETE) {
                // At least one activity is not completed.
                return false;
            }
        }

        // All activities are completed.
        return true;
    }

    public function get_description($full, $not, info $info) {
        if ($this->type === 'completion') {
            $description = get_string('condition_completion', 'availability_classmetrics', $this->percentage);
        } else if ($this->type === 'students') {
            $description = get_string('condition_students', 'availability_classmetrics', $this->minimum);
        } else {
            $description = get_string('description', 'availability_classmetrics');
        }
        
        if ($not) {
            $description = get_string('not_op', 'availability') . ' (' . $description . ')';
        }
        
        return $description;
    }

    protected function get_debug_string() {
        if ($this->type === 'completion') {
            return "completion:{$this->percentage}%";
        } else if ($this->type === 'students') {
            return "students:{$this->minimum}";
        }
        return "classmetrics";
    }

    public function is_applied_to_user_lists() {
        // This condition applies to user lists since it's based on group behavior
        return true;
    }

    public function filter_user_list(array $users, $not, info $info, \core_availability\capability_checker $checker) {
        // For class-based conditions, we don't filter individual users
        // The condition applies to the entire class
        return $users;
    }
}


