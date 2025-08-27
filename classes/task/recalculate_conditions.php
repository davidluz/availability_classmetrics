<?php

namespace availability_classmetrics\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task to recalculate availability conditions
 */
class recalculate_conditions extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_recalculate', 'availability_classmetrics');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        
        mtrace('Starting availability_classmetrics condition recalculation...');
        
        // Get all courses that have availability conditions using our plugin
        $sql = "SELECT DISTINCT c.id, c.shortname, c.fullname
                FROM {course} c
                JOIN {course_modules} cm ON cm.course = c.id
                WHERE cm.availability LIKE '%classmetrics%'
                   OR EXISTS (
                       SELECT 1 FROM {course_sections} cs 
                       WHERE cs.course = c.id 
                       AND cs.availability LIKE '%classmetrics%'
                   )";
        
        $courses = $DB->get_records_sql($sql);
        
        $count = 0;
        foreach ($courses as $course) {
            try {
                // Clear cache for this course to force recalculation
                rebuild_course_cache($course->id, true);
                
                // Clear coursemodinfo cache
                $cache = \cache::make('core', 'coursemodinfo');
                $cache->delete($course->id);
                
                $count++;
                mtrace("Recalculated conditions for course: {$course->shortname}");
                
            } catch (\Exception $e) {
                mtrace("Error recalculating conditions for course {$course->shortname}: " . $e->getMessage());
            }
        }
        
        mtrace("Completed availability_classmetrics recalculation for {$count} courses.");
    }
}

