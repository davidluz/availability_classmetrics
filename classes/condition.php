<?php
namespace availability_classmetrics;

defined('MOODLE_INTERNAL') || die();

use availability_classmetrics\local\evaluator;

class condition extends \core_availability\condition {

    /** @var string 'percent' | 'minstudents' */
    private $rule = 'percent';
    /** @var int[] */
    private $activities = [];
    /** @var string 'all' | 'any' */
    private $aggregation = 'all';
    /** @var int 0..100 */
    private $percent = 0;
    /** @var int >=0 */
    private $minstudents = 0;
    /** @var int groupid or 0 */
    private $groupid = 0;

    public function __construct($structure) {
        // Valida JSON salvo.
        if (isset($structure->rule) && in_array($structure->rule, ['percent','minstudents'])) {
            $this->rule = $structure->rule;
        }
        $this->groupid = isset($structure->groupid) ? (int)$structure->groupid : 0;

        if ($this->rule === 'percent') {
            $this->activities  = array_map('intval', isset($structure->activities) ? (array)$structure->activities : []);
            $this->aggregation = (isset($structure->aggregation) && $structure->aggregation === 'any') ? 'any' : 'all';
            $this->percent     = max(0, min(100, (int)($structure->percent ?? 0)));
        } else {
            $this->minstudents = max(0, (int)($structure->minstudents ?? 0));
        }
    }

    public function save() {
        $o = (object)[
            'type'    => 'classmetrics',
            'rule'    => $this->rule,
            'groupid' => $this->groupid,
        ];
        if ($this->rule === 'percent') {
            $o->activities  = array_values(array_unique(array_map('intval', $this->activities)));
            $o->aggregation = $this->aggregation;
            $o->percent     = $this->percent;
        } else {
            $o->minstudents = $this->minstudents;
        }
        return $o;
    }

    protected function get_debug_string() {
        if ($this->rule === 'percent') {
            return "percent:{$this->percent}, agg:{$this->aggregation}, acts:[" . implode(',', $this->activities) . "], gid:{$this->groupid}";
        } else {
            return "minstudents:{$this->minstudents}, gid:{$this->groupid}";
        }
    }

    public function get_description($full, $not, \core_availability\info $info) {
        $a = new \stdClass();
        $course = $info->get_course();

        $scope = $this->groupid > 0
            ? ' ' . get_string('scope_group', 'availability_classmetrics', groups_get_group_name($this->groupid))
            : ' ' . get_string('scope_course', 'availability_classmetrics');
        $a->scope = $scope;

        if ($this->rule === 'percent') {
            $a->percent = $this->percent;
            $a->agg = ($this->aggregation === 'all')
                ? get_string('aggregation_all', 'availability_classmetrics')
                : get_string('aggregation_any', 'availability_classmetrics');
            return get_string('description_percent', 'availability_classmetrics', $a);
        } else {
            $a->min = $this->minstudents;
            return get_string('description_minstudents', 'availability_classmetrics', $a);
        }
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $CFG;
        $course = $info->get_course();

        // Cache por requisição (chave estável).
        $cache = \cache::make('availability_classmetrics', 'metrics');
        $key = $this->cache_key($course->id);
        $hit = $cache->get($key);
        if (is_array($hit) && array_key_exists('result', $hit)) {
            $result = (bool)$hit['result'];
        } else {
            $result = $this->evaluate($course->id);
            $cache->set($key, ['result' => $result]);
        }

        // Considera NOT.
        return $not ? !$result : $result;
    }

   private function cache_key(int $courseid): string {
    if ($this->rule === 'percent') {
        $acts = array_values(array_unique(array_map('intval', $this->activities)));
        sort($acts, SORT_NUMERIC);
        $actskey = implode('-', $acts);
        return "c{$courseid}:gid{$this->groupid}:percent:{$this->percent}:agg:{$this->aggregation}:acts:{$actskey}";
    } else {
        return "c{$courseid}:gid{$this->groupid}:min:{$this->minstudents}";
    }
}


    private function evaluate(int $courseid): bool {
        // Conjunto base de estudantes ativos (student + matriculados ativos), possivelmente filtrados por grupo.
        $students = evaluator::get_active_students($courseid, $this->groupid);
        $denominator = count($students);

        if ($this->rule === 'minstudents') {
            return $denominator >= $this->minstudents;
        }

        // percent
        if (empty($this->activities)) {
            // Sem atividades -> condição só é verdadeira se percentual for 0.
            return $this->percent === 0;
        }

        $met = evaluator::count_students_meeting_completion($students, $this->activities, $this->aggregation);
        $needed = (int)ceil(($this->percent / 100.0) * $denominator);
        // Regra prática: se denominador=0 -> needed=0 -> true apenas quando percent=0 (arriba).
        return $met >= $needed;
    }

    public function is_applied_to_user_lists() {
        // Não filtra listas por usuário (condição depende da turma como um todo).
        return false;
    }
}
