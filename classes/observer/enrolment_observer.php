<?php

namespace availability_classmetrics\observer;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer for enrolment events
 */
class enrolment_observer {

    /**
     * Handle user enrolment created event
     *
     * @param \core\event\user_enrolment_created $event
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        
        // Clear availability cache for this course
        self::clear_availability_cache($courseid);
        
        debugging("Availability classmetrics: User {$userid} enrolled in course {$courseid}", DEBUG_DEVELOPER);
    }

    /**
     * Handle user enrolment deleted event
     *
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        
        // Clear availability cache for this course
        self::clear_availability_cache($courseid);
        
        debugging("Availability classmetrics: User {$userid} unenrolled from course {$courseid}", DEBUG_DEVELOPER);
    }

    /**
     * Handle user enrolment updated event
     *
     * @param \core\event\user_enrolment_updated $event
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        
        // Clear availability cache for this course
        self::clear_availability_cache($courseid);
        
        debugging("Availability classmetrics: User {$userid} enrolment updated in course {$courseid}", DEBUG_DEVELOPER);
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

