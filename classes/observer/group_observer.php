<?php

namespace availability_classmetrics\observer;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer for group events
 */
class group_observer {

    /**
     * Handle group member added event
     *
     * @param \core\event\group_member_added $event
     */
    public static function group_member_added(\core\event\group_member_added $event) {
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        $groupid = $event->objectid;
        
        // Clear availability cache for this course
        self::clear_availability_cache($courseid);
        
        debugging("Availability classmetrics: User {$userid} added to group {$groupid} in course {$courseid}", DEBUG_DEVELOPER);
    }

    /**
     * Handle group member removed event
     *
     * @param \core\event\group_member_removed $event
     */
    public static function group_member_removed(\core\event\group_member_removed $event) {
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        $groupid = $event->objectid;
        
        // Clear availability cache for this course
        self::clear_availability_cache($courseid);
        
        debugging("Availability classmetrics: User {$userid} removed from group {$groupid} in course {$courseid}", DEBUG_DEVELOPER);
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

