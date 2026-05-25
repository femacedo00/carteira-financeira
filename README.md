# API Carteira Financeira

Sistema de carteira financeira desenvolvido em Laravel.

## Pré-requisitos

Para rodar este projeto de forma isolada, você não precisa instalar PHP ou bancos de dados na sua máquina. Você precisará apenas de:

* [Docker Desktop](https://www.docker.com/products/docker-desktop/) rodando.
* **Usuários de Windows:** É estritamente necessário utilizar o **WSL2** (Ubuntu ou outra distribuição Linux). Não rode os comandos diretamente no PowerShell ou CMD.
* [Git](https://git-scm.com/) instalado.

### Clone o repositório
```bash
git clone [https://github.com/femacedo00/carteira-financeira.git](https://github.com/femacedo00/carteira-financeira.git)
cd carteira-financeira

### Instale as dependências
Como a pasta `vendor` (onde fica o executável do Sail) é ignorada pelo Git, precisamos baixar os pacotes do Laravel.

Se você **já tem o PHP e o Composer** instalados na sua máquina (ou no WSL), basta rodar:
```bash
composer install
```

Se não possuir o PHP local, utilize este contêiner temporário do Docker para baixar os pacotes:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### Configure as Variáveis de Ambiente
Crie o seu arquivo de configuração local:
```bash
cp .env.example .env
```

### Suba os Contêineres (Docker)
Com os pacotes instalados e o .env configurado, inicie o servidor, o banco de dados e os serviços auxiliares em segundo plano:
```bash
./vendor/bin/sail up -d
```

### Prepare a Aplicação e o Banco de Dados
Gere a chave de segurança criptográfica do Laravel e construa as tabelas do banco de dados (certifique-se de que os contêineres do passo anterior já terminaram de subir):

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh
```

A aplicação estará rodando perfeitamente e acessível em: http://localhost

### Dica: Atalho para o Sail
Para não precisar digitar `./vendor/bin/sail` antes de todo comando do Laravel, configure este alias no seu terminal Linux/WSL:
```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

A partir de agora, o seu fluxo de trabalho fica muito mais limpo.

Exemplo:
```bash
sail artisan make:migration nome_da_tabela
```

## API Endpoints

### Public Routes

**Create Account**
`POST /api/user`
* **Description:** Registers a new user in the system.
* **Rules:** Requires a valid name, unique document (CPF or CNPJ), and a password with a minimum of 8 characters.
* **Security:** Passwords are hashed using **Argon2id** for enhanced security.
* **Note:** While not required at registration, a 6-digit financial password (PIN) must be created later to perform any monetary transactions.

**Login**
`POST /api/login`
* **Description:** Authenticates the user to access the system's core features.
* **Rules:** Requires the registered CPF/CNPJ and password. 
* **Security:** Uses **Laravel Sanctum** for authentication. The token cannot be decrypted since sensitive session data is strictly stored in the database, not in the token itself. Logging in automatically destroys any previous active sessions.
* **Timeout:** Sessions expire automatically after 10 minutes of inactivity.

---

### Authenticated Routes
*Requires `Authorization: Bearer {token}` header.*

**User Profile**
`GET /api/me`
* **Description:** Retrieves the authenticated user's details.
* **Returns:** User's name, document (CPF/CNPJ), and current wallet balance.

**Setup/Update Financial Password**
`PATCH /api/financial-password`
* **Description:** Creates or updates the 6-digit financial password.
* **Rules:** This PIN is strictly required for executing cash-out operations (transfers and refunds).

**Logout**
`GET /api/logout`
* **Description:** Revokes the current authentication token. The user will need to log in again to access the system.

#### Cash-In & Cash-Out Operations

**Deposit**
`POST /api/deposit`
* **Description:** Adds funds to the user's account.
* **Rules:** The system automatically evaluates and applies daily and nocturnal deposit limits based on the current time.
* **Returns:** A unique transaction `token` (UUID) which is required if the user wishes to refund this specific deposit later.

**Deposit Refund**
`POST /api/deposit-refund`
* **Description:** Reverses a previously made deposit, deducting the amount from the balance.
* **Rules:** Requires the transaction `token` generated during the deposit and the 6-digit financial password. A deposit can only be refunded once.

**Transfer (P2P)**
`POST /api/transfer`
* **Description:** Transfers funds from the authenticated user to another account.
* **Rules:** Validates if the sender has sufficient balance. The system also strictly enforces dynamic daily and nocturnal transfer limits depending on the time of the request.

**Transfer Refund**
`POST /api/transfer-refund`
* **Description:** Reverses a completed P2P transfer, returning the funds to the original sender.
* **Rules:** This action can only be requested by the original sender or the receiver of the specific transaction. It requires the original transaction `token` and the user's 6-digit financial password. The refund can only be executed once.