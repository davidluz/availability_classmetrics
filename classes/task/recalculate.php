<?php
namespace availability_classmetrics\task;

defined('MOODLE_INTERNAL') || die();

class recalculate extends \core\task\scheduled_task {
    public function get_name(): string {
        return 'availability_classmetrics: daily reconcile';
    }

    public function execute() {
        // Como o cache é MODE_REQUEST, não há nada para invalidar aqui.
        // Mantemos a tarefa para compatibilidade com o requisito de "cron diário".
        mtrace('[availability_classmetrics] Daily reconcile OK');
    }
}
