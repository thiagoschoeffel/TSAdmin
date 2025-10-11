# TSAdmin

Sistema de administração para gestão de clientes e usuários com interface moderna usando Laravel e Vue.js.

## Requisitos

-   PHP 8.1+
-   Composer
-   Node.js 18+
-   PostgreSQL

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

-   Gestão completa de clientes
-   Sistema de usuários com permissões
-   Interface moderna com Vue.js + Inertia.js
-   Autenticação e autorização
-   API RESTful

## Desenvolvimento

Para desenvolvimento com hot reload:

```bash
npm run dev
```

## Testes

O projeto inclui uma suíte completa de testes unitários.

### Executando os Testes

```bash
# Executar todos os testes
./vendor/bin/phpunit

# Executar apenas testes unitários
./vendor/bin/phpunit tests/Unit/

# Executar apenas testes de feature
./vendor/bin/phpunit tests/Feature/
```

### Cobertura de Código

Para gerar relatórios de cobertura de código usando Xdebug:

```bash
# Gerar relatório HTML
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage

# Gerar relatório em texto
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text

# Gerar relatório Clover (para integração com IDEs)
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-clover coverage.xml
```

**Cobertura Atual:**

-   **Classes:** 18.52% (10/54)
-   **Métodos:** 24.29% (51/210)
-   **Linhas:** 21.72% (383/1763)

O relatório HTML será gerado no diretório `coverage/index.html`.

### Testes Incluídos

-   **Models**: User, Client, Address, Product, Order, OrderItem, ProductComponent
-   **Controllers**: ClientController (21 testes funcionais), ProductController (autorização completa)
-   **Form Requests**: StoreClientRequest, UpdateClientRequest
-   **Policies**: ClientPolicy, UserPolicy, ProductPolicy, OrderPolicy e outras policies de autorização
-   **Middlewares**: Authenticate, HandleInertiaRequests

### Sistema de Autorização

O sistema utiliza **Laravel Policies** para controle de acesso:

-   **Sem middlewares redundantes**: Todas as verificações de permissão são feitas diretamente nos controllers usando `$this->authorize()`
-   **Policies implementadas**: UserPolicy, ClientPolicy, ProductPolicy, OrderPolicy, AddressPolicy
-   **Permissões granulares**: Cada operação (view, create, update, delete) é verificada individualmente
-   **Consistência**: Todos os controllers seguem o mesmo padrão de autorização

## Licença

Este projeto está licenciado sob a MIT License.
