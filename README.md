# 🇧🇷 API Carteira Financeira

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
Com os pacotes instalados e o ´.env´ configurado, inicie o servidor, o banco de dados e os serviços auxiliares em segundo plano:
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

## Endpoints da API

### Rotas Públicas

**Criar Conta (Cadastro)**
`POST /api/user`
* **Descrição:** Registra um novo usuário no sistema.
* **Regras:** Exige um nome válido, documento único (CPF ou CNPJ) e uma senha com no mínimo 8 caracteres.
* **Segurança:** As senhas são criptografadas utilizando **Argon2id** para elevar a segurança.
* **Nota:** Embora não seja solicitada no momento do cadastro, será necessário criar posteriormente uma senha financeira (PIN) de 6 números para realizar qualquer movimentação bancária.

**Login**
`POST /api/login`
* **Descrição:** Autentica o usuário para acessar as funcionalidades do sistema.
* **Regras:** Necessita do CPF/CNPJ e da senha para efetuar a autenticação.
* **Segurança:** A autenticação escolhida foi o **Laravel Sanctum** para garantir maior segurança, já que o token não pode ser descriptografado (nenhuma informação sensível está presente no token, apenas no banco de dados, onde é vinculado). O login destrói automaticamente qualquer sessão anterior.
* **Tempo limite:** A sessão expira automaticamente após 10 minutos.

---

### Rotas Autenticadas
*Requer o cabeçalho `Authorization: Bearer {token}`.*

**Perfil do Usuário (Me)**
`GET /api/me`
* **Descrição:** Retorna os detalhes do usuário autenticado.
* **Retorno:** Nome, documento (CPF/CNPJ) e o saldo atual da carteira.

**Configurar/Atualizar Senha Financeira**
`PATCH /api/financial-password`
* **Descrição:** Cria ou atualiza a senha financeira de 6 números.
* **Regras:** Essa senha é estritamente necessária para realizar transações.

**Logout**
`GET /api/logout`
* **Descrição:** Desfaz a autenticação atual. Será necessário fazer o login novamente para acessar o sistema.

#### Operações Financeiras

**Depósito**
`POST /api/deposit`
* **Descrição:** Adiciona saldo à conta do usuário.
* **Regras:** O sistema avalia e aplica automaticamente limites de depósito, variando de acordo com o dia e a noite.
* **Retorno:** Retorna um `token` único da transação que será necessário caso o usuário queira realizar um reembolso dessa ação no futuro.

**Reembolso de Depósito**
`POST /api/deposit-refund`
* **Descrição:** Estorna um depósito realizado anteriormente, deduzindo o valor do saldo.
* **Regras:** Caso o usuário queira realizar o reembolso, é necessário informar o `token` gerado no momento do depósito e a senha financeira. Um depósito só pode ser reembolsado uma única vez.

**Transferência (P2P)**
`POST /api/transfer`
* **Descrição:** Transfere fundos do usuário autenticado para outra conta.
* **Regras:** O usuário pode transferir parte ou todo o seu saldo, mas nunca mais do que possui. A transação depende se o saldo desejado é menor ou igual ao limite de transferência que ele pode realizar naquele momento (dia/noite).

**Reembolso de Transferência**
`POST /api/transfer-refund`
* **Descrição:** Estorna uma transferência, fazendo com que o dinheiro volte para o seu local de origem.
* **Regras:** Tanto o usuário que enviou quanto quem recebeu o dinheiro podem pedir o estorno, mas a ação é restrita apenas a esses dois. Exige o `token` da transação original e a senha financeira. Assim como o depósito, o reembolso pode ser realizado apenas uma vez.

# 🇺🇸 Financial Wallet API

Financial wallet system developed in Laravel.

## Prerequisites

To run this project in an isolated environment, you don't need to install PHP or databases on your local machine. You will only need:

* [Docker Desktop](https://www.docker.com/products/docker-desktop/) running.
* **Windows Users:** It is strictly necessary to use **WSL2** (Ubuntu or another Linux distribution). Do not run the commands directly in PowerShell or CMD.
* [Git](https://git-scm.com/) installed.

### Clone the repository
```bash
git clone [https://github.com/femacedo00/carteira-financeira.git](https://github.com/femacedo00/carteira-financeira.git)
cd carteira-financeira
```

Install dependencies

Since the `vendor` folder (where the Sail executable is located) is ignored by Git, we need to download the Laravel packages.

If you already have PHP and Composer installed on your machine (or in WSL), just run:
```bash
composer install
```

If you do not have PHP locally, use this temporary Docker container to download the packages:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### Configure Environment Variables
Create your local configuration file:
```bash
cp .env.example .env
```

### Start the Containers (Docker)
With the packages installed and the .env configured, start the server, database, and auxiliary services in the background:
```bash
./vendor/bin/sail up -d
```

### Prepare the Application and Database
Generate the Laravel cryptographic security key and build the database tables (ensure the containers from the previous step have fully started):

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh
```

The application will be running perfectly and accessible at: http://localhost

### Tip: Sail Shortcut
So you don't have to type ./vendor/bin/sail before every Laravel command, configure this alias in your Linux/WSL terminal:
```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

From now on, your workflow gets much cleaner.

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