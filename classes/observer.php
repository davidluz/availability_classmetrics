<?php
namespace availability_classmetrics;

defined('MOODLE_INTERNAL') || die();

class observer {
    /**
     * Observador genérico: hoje não precisamos invalidar nada além do request,
     * mas deixamos como ponto de extensão se no futuro usar MODE_APPLICATION.
     */
    public static function bump(\core\event\base $event): void {
        // No-op por enquanto (request cache se resolve sozinho por requisição).
        // Se migrar para MODE_APPLICATION, faça:
        // $cache = \cache::make('availability_classmetrics', 'metrics');
        // $cache->purge();
    }
}
