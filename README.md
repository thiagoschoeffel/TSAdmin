# TSAdmin

Sistema de administração para gestão de clientes e usuários com interface moderna usando Laravel e Vue.js.

## Requisitos

- PHP 8.1+
- Composer
- Node.js 18+
- PostgreSQL

## Instalação

1. Clone o repositório:
```bash
git clone <repository-url>
cd tsadmin
```

2. Instale as dependências do PHP:
```bash
composer install
```

3. Configure o ambiente:
```bash
cp .env.example .env
```

4. Configure as variáveis de ambiente no `.env`:
   - `APP_NAME=TSAdmin`
   - Configure a conexão com o banco de dados PostgreSQL
   - Configure outras variáveis conforme necessário

5. Gere a chave da aplicação:
```bash
php artisan key:generate
```

6. Execute as migrações:
```bash
php artisan migrate
```

7. Instale as dependências do Node.js:
```bash
npm install
```

8. Compile os assets:
```bash
npm run build
```

9. Inicie o servidor:
```bash
php artisan serve
```

## Funcionalidades

- Gestão completa de clientes
- Sistema de usuários com permissões
- Interface moderna com Vue.js + Inertia.js
- Autenticação e autorização
- API RESTful

## Desenvolvimento

Para desenvolvimento com hot reload:
```bash
npm run dev
```

## Contribuição

Sinta-se à vontade para contribuir com o projeto! Faça um fork e envie um pull request.

## Licença

Este projeto está licenciado sob a MIT License.
