<?php

namespace availability_classmetrics\observer;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer for completion events
 */
class completion_observer {

    /**
     * Handle course module completion updated event
     *
     * @param \core\event\course_module_completion_updated $event
     */
    public static function completion_updated(\core\event\course_module_completion_updated $event) {
        global $DB;
        
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        
        // Clear availability cache for this course
        self::clear_availability_cache($courseid);
        
        // Log the event for debugging
        debugging("Availability classmetrics: Completion updated for user {$userid} in course {$courseid}", DEBUG_DEVELOPER);
    }

    /**
     * Clear availability cache for a course
     *
     * @param int $courseid
     */
    private static function clear_availability_cache($courseid) {
        // Clear the course cache to force recalculation of availability conditions
        rebuild_course_cache($courseid, true);
        
        // Also clear user-specific caches if needed
        $cache = \cache::make('core', 'coursemodinfo');
        $cache->delete($courseid);
    }
}

