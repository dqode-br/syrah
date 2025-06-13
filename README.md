# SYRAH - Sistema ERP

Sistema ERP desenvolvido em PHP com MySQL e interface moderna em HTML/CSS/JavaScript.

## Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Composer (opcional, para gerenciamento de dependências)

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/syrah.git
cd syrah
```

2. Configure o banco de dados:
   - Crie um banco de dados MySQL chamado `syrah_db`
   - Importe o arquivo `database/syrah_db.sql` para criar as tabelas
   - Ajuste as credenciais do banco no arquivo `config/database.php`

3. Configure o servidor web:
   - Configure o DocumentRoot para apontar para a pasta do projeto
   - Certifique-se que o mod_rewrite está habilitado (Apache)
   - Configure as permissões corretas nas pastas

4. Acesse o sistema:
   - URL: http://localhost/syrah
   - Usuário padrão: admin@syrah.com
   - Senha padrão: admin123

## Estrutura do Projeto

```
syrah/
├── api/                    # Endpoints da API
│   ├── auth.php           # Autenticação
│   ├── clientes.php       # CRUD de clientes
│   ├── fornecedores.php   # CRUD de fornecedores
│   ├── documentos.php     # CRUD de documentos
│   └── contas.php         # CRUD de contas
├── config/                 # Configurações
│   └── database.php       # Configuração do banco
├── database/              # Scripts SQL
│   └── syrah_db.sql       # Estrutura do banco
├── assets/                # Arquivos estáticos
│   ├── css/              # Estilos
│   ├── js/               # Scripts
│   └── images/           # Imagens
└── index_modificado.html  # Interface principal
```

## Funcionalidades

- Autenticação de usuários
- Dashboard com indicadores
- Gestão de clientes
- Gestão de fornecedores
- Gestão de documentos
- Gestão financeira
- Interface responsiva
- Tema escuro

## Segurança

- Senhas criptografadas com bcrypt
- Proteção contra SQL Injection
- Headers de segurança configurados
- Validação de dados
- Controle de acesso

## Desenvolvimento

Para contribuir com o projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature
3. Faça commit das alterações
4. Envie um pull request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes. 