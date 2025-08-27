<?php

namespace availability_classmetrics;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for availability_classmetrics condition.
 *
 * @package availability_classmetrics
 * @copyright 2023
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition_test extends \advanced_testcase {

    /**
     * Test condition construction and saving.
     */
    public function test_condition_construction() {
        $this->resetAfterTest();

        // Test completion condition
        $structure = (object)[
            'type' => 'classmetrics',
            'conditiontype' => 'completion',
            'percentage' => 80,
            'activities' => '1,2,3'
        ];

        $condition = new condition($structure);
        $saved = $condition->save();

        $this->assertEquals('classmetrics', $saved->type);
        $this->assertEquals('completion', $saved->conditiontype);
        $this->assertEquals(80, $saved->percentage);
        $this->assertEquals('1,2,3', $saved->activities);
    }

    /**
     * Test students condition.
     */
    public function test_students_condition() {
        $this->resetAfterTest();

        $structure = (object)[
            'type' => 'classmetrics',
            'conditiontype' => 'students',
            'minimum' => 15
        ];

        $condition = new condition($structure);
        $saved = $condition->save();

        $this->assertEquals('students', $saved->conditiontype);
        $this->assertEquals(15, $saved->minimum);
    }

    /**
     * Test condition with group filter.
     */
    public function test_condition_with_group() {
        $this->resetAfterTest();

        $structure = (object)[
            'type' => 'classmetrics',
            'conditiontype' => 'completion',
            'percentage' => 90,
            'activities' => '1',
            'groupid' => 5
        ];

        $condition = new condition($structure);
        $saved = $condition->save();

        $this->assertEquals(5, $saved->groupid);
    }

    /**
     * Test debug string generation.
     */
    public function test_debug_string() {
        $this->resetAfterTest();

        // Test completion condition debug string
        $structure = (object)[
            'conditiontype' => 'completion',
            'percentage' => 75
        ];
        $condition = new condition($structure);
        $this->assertEquals('completion:75%', $condition->get_debug_string());

        // Test students condition debug string
        $structure = (object)[
            'conditiontype' => 'students',
            'minimum' => 20
        ];
        $condition = new condition($structure);
        $this->assertEquals('students:20', $condition->get_debug_string());
    }

    /**
     * Test that condition applies to user lists.
     */
    public function test_applies_to_user_lists() {
        $this->resetAfterTest();

        $structure = (object)[
            'conditiontype' => 'completion',
            'percentage' => 50
        ];
        $condition = new condition($structure);
        
        $this->assertTrue($condition->is_applied_to_user_lists());
    }
}

