# StudyFlow — Sistema de Estudos para Vestibulares (TCC)

Sistema em **PHP + HTML + CSS + JavaScript** que gera automaticamente um ciclo de
estudos com base no peso das matérias do curso escolhido e no tempo disponível
do aluno, além de flashcards com repetição espaçada.

## Telas incluídas nesta entrega
- **Landing Page** (`index.php`) — apresentação pública do sistema
- **Login / Cadastro** (`login.php` / `register.php`)
- **Onboarding** (`onboarding.php`) — escolha de vestibular, curso e horas
  disponíveis por semana; gera o ciclo automaticamente
- **Ciclo de Estudos** (`study_cycles.php`) — matérias com tempo calculado pelo
  peso, organizadas numa agenda semanal por arrastar-e-soltar
- **Flashcards** (`flashcards.php`) — biblioteca, criação e revisão com
  repetição espaçada (fácil/médio/difícil)

## Como rodar

### Opção 1: XAMPP / WampServer / Laragon
1. Copie a pasta `studyflow` para dentro de `htdocs` (XAMPP) ou `www` (Laragon).
2. Confirme que a extensão `pdo_sqlite` está habilitada no seu `php.ini`
   (vem habilitada por padrão na maioria das instalações).
3. Acesse `http://localhost/studyflow/` no navegador.
4. O banco (`database/studyflow.sqlite`) é criado automaticamente na primeira
   requisição, já com vestibulares, cursos, matérias e pesos de exemplo.

### Opção 2: servidor embutido do PHP
```bash
cd studyflow
php -S localhost:8000
```
Depois acesse `http://localhost:8000`.

## Banco de dados
Por padrão o sistema usa **SQLite** (arquivo único, zero configuração) para
facilitar a demonstração. Para usar **MySQL** (como no schema original do TCC),
troque a conexão em `includes/db.php` pelo DSN comentado no topo do arquivo e
adapte a sintaxe SQL específica do SQLite (`DATE('now', '+N days')`,
`ON CONFLICT ... DO UPDATE`) para o equivalente em MySQL.

## Dados de exemplo já cadastrados
- Vestibulares: ENEM 2027, FUVEST 2027, FATEC 2027.1
- Cursos: Medicina, Direito, Engenharia de Computação, Análise e
  Desenvolvimento de Sistemas, Gestão Empresarial — cada um com pesos de
  matérias já configurados em `includes/db.php`
- Você pode editar esses pesos diretamente no array `$pesos` de `db.php`
  antes da primeira execução (apague o arquivo `.sqlite` se já tiver rodado
  uma vez, para os dados de exemplo serem recriados)

## Cálculo do ciclo de estudos
```
minutos_matéria = (peso_matéria / soma_dos_pesos_do_curso) × horas_semana × 60
```
Cada matéria recebe um tempo proporcional ao seu peso no curso escolhido.

## Próximos passos sugeridos (fora do escopo desta entrega)
- Tela de Desempenho (gráficos com `sessoes_estudo` e
  `historico_revisoes_flashcard`)
- Tela de Perfil / edição de dados do aluno
- Painel geral (Dashboard) com tarefas do dia
