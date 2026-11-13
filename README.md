# Wave_Admin

# Wave Admin

Sistema web de administração de uma plataforma musical, desenvolvido como projeto acadêmico. O Wave Admin permite gerenciar usuários, artistas, álbuns, músicas e gêneros musicais, além de registrar atividades administrativas e oferecer autenticação em duas etapas (MFA).

## Funcionalidades

- **Usuários:** cadastro, consulta, edição e gerenciamento de status.
- **Artistas:** cadastro e gerenciamento de artistas.
- **Álbuns:** cadastro, edição e exclusão de álbuns, com associação a artistas e capas.
- **Músicas:** cadastro e gerenciamento de músicas, com associação a artistas, álbuns e gêneros.
- **Gêneros musicais:** cadastro, edição e exclusão.
- **Logs administrativos:** registro e consulta de ações realizadas no sistema.
- **Autenticação MFA:** camada adicional de segurança no acesso administrativo.

## Tecnologias utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- Bootstrap 5
- Apache (XAMPP)
- phpMyAdmin

## Estrutura do projeto

```text
WAVE_ADMIN/
├── app/
│   ├── config/
│   ├── helpers/
│   └── middleware/
├── public/
│   ├── assets/
│   ├── uploads/
│   └── arquivos PHP
├── database/
│   └── wave_admin.sql
├── .gitignore
└── README.md
```

## Como executar o projeto

### 1. Pré-requisitos

- XAMPP ou ambiente equivalente;
- PHP 8.x;
- MySQL ou MariaDB;
- Navegador de internet.

### 2. Clonar o repositório

```bash
git clone URL_DO_REPOSITORIO
```

### 3. Configurar o projeto

Coloque a pasta `WAVE_ADMIN` dentro do diretório `htdocs` do XAMPP:

```text
C:\xampp\htdocs\
```

### 4. Criar o banco de dados

1. Inicie Apache e MySQL no XAMPP.
2. Acesse `http://localhost/phpmyadmin`.
3. Crie um banco chamado `wave_admin`.
4. Selecione o banco e importe o arquivo `database/wave_admin.sql`.

### 5. Configurar a conexão

Configure o arquivo:

```text
app/config/database.php
```

Utilize as credenciais do MySQL do seu ambiente local.

Exemplo:

```php
$host = 'localhost';
$dbname = 'wave_admin';
$username = 'root';
$password = '';
```

Ajuste os dados conforme sua instalação.

**Importante:** não publique senhas reais, segredos MFA ou credenciais pessoais no GitHub. O arquivo de configuração com credenciais locais deve ficar fora do versionamento.

### 6. Acessar o sistema

Com Apache e MySQL em execução, acesse:

```text
http://localhost/WAVE_ADMIN/public/
```

Utilize uma conta administrativa válida para entrar no sistema.

## Banco de dados

O sistema utiliza o banco `wave_admin`, com tabelas para administradores, usuários, artistas, álbuns, músicas, gêneros musicais, logs e tokens relacionados à autenticação em duas etapas.

O arquivo SQL deve conter a estrutura e, quando apropriado, dados de demonstração. Antes de publicar, confira se não existem senhas, segredos MFA ou tokens reais no arquivo.

## Segurança

- Controle de autenticação nas páginas administrativas;
- Proteção CSRF em formulários;
- Tratamento de dados para evitar conteúdo HTML não confiável;
- Registro de atividades administrativas;
- Autenticação em duas etapas (MFA).

## Desenvolvimento em grupo

- Cada integrante deve configurar seu próprio banco de dados local.
- Alterações na estrutura do banco devem ser compartilhadas com o grupo.
- Não enviar credenciais, arquivos temporários ou configurações pessoais.
- Testar os módulos após realizar alterações.

## Projeto acadêmico

O Wave Admin foi desenvolvido para fins acadêmicos e de aprendizagem, com foco na aplicação prática de desenvolvimento web, integração PHP com MySQL, operações CRUD, organização de código e fundamentos de segurança.

**Atenção antes de publicar:** confirme que o arquivo `database/wave_admin.sql` foi exportado do seu phpMyAdmin e que não contém segredos MFA ou dados privados. Também confira se o exemplo de conexão corresponde à estrutura real do seu `database.php`.
