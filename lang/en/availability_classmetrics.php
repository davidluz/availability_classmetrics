<?php
// Strings for availability_classmetrics (Moodle 4.5).

$string['pluginname'] = 'Class metrics (availability)';
$string['title'] = 'Class metrics';
$string['description'] = 'Restrict access based on class metrics (completion percentage and class size).';

$string['rule'] = 'Rule';
$string['rule_percent'] = 'Completion % (class)';
$string['rule_minstudents'] = 'Minimum students (class)';

$string['activities'] = 'Target activities';
$string['aggregation'] = 'Aggregation across activities';
$string['aggregation_all'] = 'ALL';
$string['aggregation_any'] = 'ANY';
$string['percent'] = 'Minimum percentage (%)';
$string['minstudents'] = 'Minimum number of students';
$string['group'] = 'Filter by group (optional)';
$string['nogroup'] = '— Whole course —';

$string['description_percent'] = 'Available when at least {$a->percent}% of the class have completed {$a->agg} of the selected activities {$a->scope}.';
$string['description_minstudents'] = 'Available when there are at least {$a->min} active students {$a->scope}.';
$string['scope_group'] = '(group: {$a})';
$string['scope_course'] = '(whole course)';

$string['error_noactivities'] = 'Select at least one activity with completion enabled.';
$string['error_percent'] = 'Enter a percentage between 0 and 100.';
$string['error_minstudents'] = 'Enter a minimum number of students (≥ 0).';

$string['privacy:metadata'] = 'The availability_classmetrics plugin does not store any personal data.';
