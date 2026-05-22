# API Carteira Financeira

Sistema de carteira financeira desenvolvido em Laravel.

## Pré-requisitos

Para rodar este projeto de forma isolada, você não precisa instalar PHP ou bancos de dados na sua máquina. Você precisará apenas de:

* [Docker Desktop](https://www.docker.com/products/docker-desktop/) rodando.
* **Usuários de Windows:** É estritamente necessário utilizar o **WSL2** (Ubuntu ou outra distribuição Linux). Não rode os comandos diretamente no PowerShell ou CMD.
* [Git](https://git-scm.com/) instalado.

### Clone o repositório
```bash
git clone [https://github.com/SEU_USUARIO/carteira-financeira.git](https://github.com/SEU_USUARIO/carteira-financeira.git)
cd carteira-financeira

### Instale as dependências
Como a pasta `vendor` (onde fica o executável do Sail) é ignorada pelo Git, precisamos baixar os pacotes do Laravel.

Se você **já tem o PHP e o Composer** instalados na sua máquina (ou no WSL), basta rodar:
```bash
composer install

Se não possuir o PHP local, utilize este contêiner temporário do Docker para baixar os pacotes:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

### Configure as Variáveis de Ambiente
Crie o seu arquivo de configuração local:
```bash
cp .env.example .env

### Suba os Contêineres (Docker)
Com os pacotes instalados e o .env configurado, inicie o servidor, o banco de dados e os serviços auxiliares em segundo plano:
```bash
./vendor/bin/sail up -d

### Prepare a Aplicação e o Banco de Dados
Gere a chave de segurança criptográfica do Laravel e construa as tabelas do banco de dados (certifique-se de que os contêineres do passo anterior já terminaram de subir):

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh

A aplicação estará rodando perfeitamente e acessível em: http://localhost

### Dica: Atalho para o Sail
Para não precisar digitar `./vendor/bin/sail` antes de todo comando do Laravel, configure este alias no seu terminal Linux/WSL:
```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'

A partir de agora, o seu fluxo de trabalho fica muito mais limpo.

Exemplo:
```bash
sail artisan make:migration nome_da_tabela