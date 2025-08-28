<?php
// Strings para availability_classmetrics (Moodle 4.5).

$string['pluginname'] = 'Métricas da turma (disponibilidade)';
$string['title'] = 'Métricas da turma';
$string['description'] = 'Restrinja o acesso com base em métricas da turma (percentual de conclusão e tamanho da turma).';

$string['rule'] = 'Regra';
$string['rule_percent'] = '% de conclusão (turma)';
$string['rule_minstudents'] = 'Nº mínimo de alunos (turma)';

$string['activities'] = 'Atividades-alvo';
$string['aggregation'] = 'Agregação entre atividades';
$string['aggregation_all'] = 'TODAS';
$string['aggregation_any'] = 'QUALQUER';
$string['percent'] = 'Percentual mínimo (%)';
$string['minstudents'] = 'Número mínimo de alunos';
$string['group'] = 'Filtrar por grupo (opcional)';
$string['nogroup'] = '— Curso inteiro —';

$string['description_percent'] = 'Disponível quando pelo menos {$a->percent}% da turma concluir {$a->agg} as atividades selecionadas {$a->scope}.';
$string['description_minstudents'] = 'Disponível quando houver pelo menos {$a->min} alunos ativos {$a->scope}.';
$string['scope_group'] = '(grupo: {$a})';
$string['scope_course'] = '(curso inteiro)';

$string['error_noactivities'] = 'Selecione ao menos 1 atividade com conclusão habilitada.';
$string['error_percent'] = 'Informe um percentual entre 0 e 100.';
$string['error_minstudents'] = 'Informe um número mínimo de alunos (≥ 0).';

$string['privacy:metadata'] = 'O plugin availability_classmetrics não armazena dados pessoais.';
