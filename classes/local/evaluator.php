<?php
namespace availability_classmetrics\local;

defined('MOODLE_INTERNAL') || die();

class evaluator {
    /**
     * Retorna o conjunto de userids que contam como "estudantes ativos" no curso,
     * e opcionalmente filtrados por grupo.
     *
     * Critérios:
     *  - user_enrolments ativos e válidos no tempo;
     *  - enrol.status = 0;
     *  - papel com archetype 'student' (preferencialmente) ou shortname 'student' na context do curso;
     *  - se $groupid > 0, restringe a membros do grupo.
     */
    public static function get_active_students(int $courseid, int $groupid = 0): array {
        global $DB;

        $coursectx = \context_course::instance($courseid);

        // Descobre papéis com archetype=student (fallback: shortname=student).
        $roleids = $DB->get_fieldset_select('role', 'id', "archetype = :arch", ['arch' => 'student']);
        if (empty($roleids)) {
            $fallback = $DB->get_field('role', 'id', ['shortname' => 'student']);
            if ($fallback) {
                $roleids = [$fallback];
            }
        }
        if (empty($roleids)) {
            // Não há "student" definido – não consideramos ninguém.
            return [];
        }

        $now = time();
        $params = [
            'courseid' => $courseid,
            'ctxid'    => $coursectx->id,
            'now1'     => $now,
            'now2'     => $now,
            
        ];

        $groupsql = '';
        if ($groupid > 0) {
            $groupsql = "JOIN {groups_members} gm ON gm.userid = u.id AND gm.groupid = :gid";
            $params['gid'] = $groupid;
        }

        list($roleinsql, $roleinparams) = $DB->get_in_or_equal($roleids, \SQL_PARAMS_NAMED);
        $params += $roleinparams;

        $sql = "
            SELECT DISTINCT u.id
              FROM {user} u
              JOIN {role_assignments} ra
                ON ra.userid = u.id
               AND ra.contextid = :ctxid
               AND ra.roleid {$roleinsql}
              JOIN {user_enrolments} ue
                ON ue.userid = u.id
               AND ue.status = 0
               AND (ue.timeend = 0 OR ue.timeend > :now1)
               AND ue.timestart <= :now2
              JOIN {enrol} e
                ON e.id = ue.enrolid
               AND e.courseid = :courseid
               AND e.status = 0
              {$groupsql}
             WHERE u.deleted = 0 AND u.suspended = 0
        ";

        return $DB->get_fieldset_sql($sql, $params);
    }

    /**
     * Conta quantos estudantes do conjunto $userids satisfazem a condição de conclusão
     * sobre as atividades $cmids, com agregação $aggregation ('all'|'any').
     */
    public static function count_students_meeting_completion(array $userids, array $cmids, string $aggregation): int {
        global $DB;
        if (empty($userids) || empty($cmids)) {
            return 0;
        }

        list($uinsql, $uinparams) = $DB->get_in_or_equal($userids, \SQL_PARAMS_NAMED);
        list($cinsql,  $cinparams) = $DB->get_in_or_equal($cmids, \SQL_PARAMS_NAMED);

        // COMPLETION_COMPLETE(1), COMPLETION_COMPLETE_PASS(2), COMPLETION_COMPLETE_FAIL(3)
        $states = [1,2,3];
        list($sinsql, $sinparams) = $DB->get_in_or_equal($states, \SQL_PARAMS_NAMED);

        $params = $uinparams + $cinparams + $sinparams;

        if ($aggregation === 'all') {
            $sql = "
                SELECT cmc.userid
                  FROM {course_modules_completion} cmc
                 WHERE cmc.userid {$uinsql}
                   AND cmc.coursemoduleid {$cinsql}
                   AND cmc.completionstate {$sinsql}
              GROUP BY cmc.userid
                HAVING COUNT(DISTINCT cmc.coursemoduleid) = :need
            ";
            $params['need'] = count($cmids);
            $rows = $DB->get_records_sql($sql, $params);
            return count($rows);
        } else {
            // any
            $sql = "
                SELECT DISTINCT cmc.userid
                  FROM {course_modules_completion} cmc
                 WHERE cmc.userid {$uinsql}
                   AND cmc.coursemoduleid {$cinsql}
                   AND cmc.completionstate {$sinsql}
            ";
            $rows = $DB->get_fieldset_sql($sql, $params);
            return count($rows);
        }
    }
}
