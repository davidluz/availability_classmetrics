# Plugin Availability Class Metrics para Moodle 4.5

## Descrição

O plugin **Availability Class Metrics** adiciona ao Moodle a capacidade de restringir o acesso a recursos e atividades baseado no comportamento coletivo da turma, ao invés de apenas no comportamento individual dos estudantes.

Este plugin permite que professores configurem condições de acesso que dependem de:
- **Porcentagem de conclusão da turma**: Libera o acesso quando uma determinada porcentagem dos alunos completar atividades específicas
- **Número mínimo de alunos**: Libera o acesso quando um número mínimo de alunos estiver matriculado no curso

## Funcionalidades

### Condições Disponíveis

1. **Condição "% de conclusão"**
   - Configure uma ou mais atividades-alvo
   - Defina uma porcentagem mínima de conclusão
   - A condição é atendida quando ≥ N% dos estudantes com papel "student" tiverem as atividades marcadas como concluídas

2. **Condição "nº mínimo de alunos"**
   - Defina um número mínimo de alunos matriculados
   - A condição é atendida quando o número de inscritos ativos com papel "student" for ≥ X

3. **Filtro por grupo**
   - Opção de aplicar as condições apenas a membros de um grupo específico
   - Permite controlar turmas paralelas com metas diferentes

### Recálculo Automático

O plugin recalcula automaticamente as condições quando:
- Um aluno completa uma atividade
- Um usuário é matriculado ou removido do curso
- Um aluno é adicionado ou removido de um grupo
- Execução da tarefa agendada diária (às 2:00 AM)

## Requisitos

- Moodle 4.5 ou superior
- PHP 7.4 ou superior

## Instalação

### Método 1: Via Interface Web do Moodle

1. Faça o download do arquivo ZIP do plugin
2. Acesse **Administração do Site > Plugins > Instalar plugins**
3. Faça o upload do arquivo ZIP
4. Siga as instruções na tela para completar a instalação

### Método 2: Via FTP/SSH

1. Extraia o conteúdo do ZIP
2. Copie a pasta `availability_classmetrics` para `[moodle]/availability/condition/`
3. Acesse **Administração do Site > Notificações** para completar a instalação

## Configuração e Uso

### Habilitando o Plugin

1. Vá para **Administração do Site > Plugins > Disponibilidade > Gerenciar condições de disponibilidade**
2. Certifique-se de que "Class Metrics" está habilitado

### Configurando uma Condição

1. Edite qualquer atividade ou seção do curso
2. Expanda a seção **"Restringir acesso"**
3. Clique em **"Adicionar restrição"**
4. Selecione **"Métricas da Turma"**

#### Para Condição de Porcentagem de Conclusão:

1. Selecione "Porcentagem de Conclusão" no tipo de condição
2. Defina a porcentagem desejada (0-100%)
3. Marque as atividades que devem ser consideradas
4. Opcionalmente, selecione um grupo específico

#### Para Condição de Número Mínimo de Alunos:

1. Selecione "Número Mínimo de Alunos" no tipo de condição
2. Defina o número mínimo de alunos
3. Opcionalmente, selecione um grupo específico

### Exemplos de Uso

**Exemplo 1: Certificado liberado após 80% da turma completar atividades**
- Tipo: Porcentagem de Conclusão
- Porcentagem: 80%
- Atividades: Selecione as atividades obrigatórias
- Grupo: Todos os alunos

**Exemplo 2: Fórum de discussão liberado apenas com turma mínima**
- Tipo: Número Mínimo de Alunos
- Mínimo: 15 alunos
- Grupo: Todos os alunos

**Exemplo 3: Atividade especial para grupo específico**
- Tipo: Porcentagem de Conclusão
- Porcentagem: 90%
- Atividades: Atividades do módulo
- Grupo: Grupo Avançado

## Estrutura do Plugin

```
availability_classmetrics/
├── classes/
│   ├── condition.php          # Lógica principal da condição
│   ├── frontend.php           # Interface do formulário
│   ├── observer/              # Observadores de eventos
│   │   ├── completion_observer.php
│   │   ├── enrolment_observer.php
│   │   └── group_observer.php
│   └── task/
│       └── recalculate_conditions.php  # Tarefa agendada
├── db/
│   ├── events.php             # Definição de eventos
│   └── tasks.php              # Definição de tarefas
├── lang/
│   ├── en/
│   │   └── availability_classmetrics.php
│   └── pt_br/
│       └── availability_classmetrics.php
├── yui/
│   └── src/form/
│       ├── js/form.js         # JavaScript do formulário
│       └── meta/form.json     # Metadados YUI
├── version.php                # Informações da versão
├── lib.php                    # Funções auxiliares
└── README.md                  # Esta documentação
```

## Desenvolvimento

### Eventos Monitorados

O plugin monitora os seguintes eventos do Moodle:
- `\core\event\course_module_completion_updated`
- `\core\event\user_enrolment_created`
- `\core\event\user_enrolment_deleted`
- `\core\event\user_enrolment_updated`
- `\core\event\group_member_added`
- `\core\event\group_member_removed`

### Cache e Performance

- O plugin utiliza o sistema de cache do Moodle para otimizar performance
- As condições são recalculadas automaticamente quando necessário
- Uma tarefa agendada diária garante a consistência dos dados

## Solução de Problemas

### A condição não está sendo recalculada

1. Verifique se os eventos estão sendo disparados corretamente
2. Execute manualmente a tarefa agendada em **Administração do Site > Servidor > Tarefas > Tarefas agendadas**
3. Verifique os logs do sistema para erros

### A interface não aparece no formulário

1. Certifique-se de que o plugin está instalado corretamente
2. Limpe o cache do Moodle
3. Verifique se não há erros JavaScript no console do navegador

### Problemas de performance

1. Considere limitar o número de atividades monitoradas
2. Use grupos para reduzir o escopo das verificações
3. Monitore a execução da tarefa agendada

## Suporte

Para suporte técnico ou relato de bugs:
1. Verifique a documentação completa
2. Consulte os logs do Moodle para mensagens de erro
3. Entre em contato com o administrador do sistema

## Licença

Este plugin é distribuído sob a mesma licença do Moodle (GPL v3).

## Changelog

### Versão 1.0.0
- Implementação inicial
- Suporte para condições de porcentagem de conclusão
- Suporte para condições de número mínimo de alunos
- Filtro por grupos
- Recálculo automático via eventos
- Tarefa agendada para reconciliação diária
- Interface em português e inglês

