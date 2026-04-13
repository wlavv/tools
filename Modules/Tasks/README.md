# Tasks Module v2

Módulo autónomo para gestão de tarefas da família.

## Inclui
- ecrã diário por membro
- calendário mensal
- dashboard mensal
- CRUD de membros
- CRUD de tarefas
- dias da semana por tarefa
- preparação automática dos registos mensais em `wt_tasks_done`

## Tabelas
- `wt_tasks` (existente)
- `wt_tasks_done` (existente)
- `wt_task_members` (nova)

## Rotas
- `/hr/tasks`
- `/hr/tasks/dashboard/{year?}/{month?}`
- `/hr/tasks/calendar/{year}/{month}`
- `/hr/tasks/members`
- `/hr/tasks/manage`

## Notas
- A migration tenta adaptar as tabelas existentes sem destruir dados.
- O histórico anterior continua utilizável.
- Os nomes antigos hardcoded passam a poder ser geridos no módulo.


## v2.5
- gestão de prémios default
- gestão de overrides mensais
- suporte a prémios globais e por membro
