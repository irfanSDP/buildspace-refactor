# BuildSpace Refactor (IN PROGRESS)

> Status: 🚧 Work in progress / under refactor. Things will keep changing.

This repo contains multiple related apps and services that used to live separately and are now being merged into one monorepo for local development currently using php 7.4 planning to upgrade to 8.0:

- `buildspace/` – main Symfony 1.x app (`https://bq.buildspace.local`)
- `eproject/` – related app / service ( Laravel 4)
- `symfony/` – Symfony 1.x framework source (vendored / customized)
- `samlauth/` – SimpleSAMLphp service & config (acts as SP/IdP glue)
- `infra/` – local infra helpers (nginx config examples, etc.)

The goal: after cloning this repo, you can boot the full stack locally using **Laragon**, **Docker Desktop** (for PostgreSQL), and custom **nginx vhosts**.

---

## ⚠️ Important Notes

- This project is still under **refactoring**, and not production-ready.
- Do **NOT** commit:
  - real secrets or `.env` files
  - private keys or certificates
  - `authsources.php`, `config.php`, or DB credentials
- Paths were refactored from hardcoded `C:/laragon/...` to **relative paths** for portability.
- SAML authentication is temporarily bypassed for local development (`/auth/login` local login is enabled).

---

## 🧰 1. Requirements

### Recommended Setup (Windows)
| Tool | Description | Link |
|------|--------------|------|
| **Laragon** | Local PHP environment (nginx + PHP-FPM + MySQL/PostgreSQL) | [https://laragon.org](https://laragon.org) |
| **Docker Desktop** | Runs PostgreSQL locally | [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/) |
| **Git** | For cloning this repo | [https://git-scm.com/downloads](https://git-scm.com/downloads) |
| **Composer** | PHP dependency manager | [https://getcomposer.org/download/](https://getcomposer.org/download/) |

✅ Optional:
- **DBeaver** / **pgAdmin** – GUI for PostgreSQL  
- **VS Code** – Recommended IDE  
- **mkcert** – To generate local HTTPS certs if not using Laragon defaults

---

## 📦 2. Clone the Repository

```bash
git clone https://github.com/irfanSDP/buildspace-refactor.git
cd buildspace-refactor

```

## 📦 3. Start PostgreSQL (Docker)

```bash
docker run -d --name buildspace-postgres ^
  -e POSTGRES_USER=buildspace ^
  -e POSTGRES_PASSWORD=buildspace ^
  -e POSTGRES_DB=buildspace_dev ^
  -p 5432:5432 ^
  postgres:15

You can verify:
docker ps

can also ON the Container in your docker desktop via PCK docker compose setup

```

## 📦 4. Hosts Configuration

```bash
C:\Windows\System32\drivers\etc\hosts

Add:
127.0.0.1   etender.buildspace.local
127.0.0.1   bq.buildspace.local
127.0.0.1   auth.buildspace.local

These map your local nginx vhosts for:
bq.buildspace.local → main Symfony app
auth.buildspace.local → optional SimpleSAML IdP

```

## 📦 5. Laragon nginx Setup

```bash
infra/nginx/
 ├─ bq.buildspace.local.conf.example
 └─ auth.buildspace.local.conf.example

 Steps:

Copy these to Laragon nginx sites:

C:\laragon\etc\nginx\sites-enabled\

bq.buildspace.local.conf
auth.buildspace.local.conf

Update file paths:

root  "C:/laragon/www/buildspace-refactor/buildspace/web";
ssl_certificate     "C:/laragon/certs/bq.buildspace.local.pem";
ssl_certificate_key "C:/laragon/certs/bq.buildspace.local-key.pem";


Restart nginx via Laragon GUI or command:

laragon restart

```

## 📦 6. Install PHP Dependencies (Composer)

```bash

Please upload the PHP 7.4 directory package from teams currently still in progress

cd buildspace
composer install

cd ../eproject
composer install

cd ..

```

## 📦 7. Local Configuration Files

```bash
cd buildspace/config
copy databases.yml.example databases.yml

Then edit:

all:
  doctrine:
    class: sfDoctrineDatabase
    param:
      dsn: "pgsql:host=localhost;port=5432;dbname=buildspace_dev"
      username: buildspace
      password: buildspace

For samlauth:
cd samlauth/config
copy config.php.example config.php
copy authsources.php.example authsources.php

```

## 📦 8. Authentication Flow (Local Dev Mode)

```bash
Currently replaced bq (Symfony) full SAML authentication with a local dev login:

Routes

GET /auth/login → basic login form

POST /auth/do-login → validates user via sfGuardUser

GET /auth/logout → destroys session and redirects to login

Logic

If valid user → $this->getUser()->signIn($user, false)

On success → redirect to /

On failure → re-render login with error message

In myUser::initialize(), the SAML check is disabled for local dev to prevent forced redirects to auth.buildspace.local.

```





