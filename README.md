# 🚀 Desafio Técnico: Classificação de Logs DNS usando IA

## 📌 Sobre o Projeto
Este projeto processa logs de consultas DNS e os classifica em níveis de risco usando uma API de Inteligência Artificial (OpenAI ou Groq). O backend é Laravel, o frontend é Vue 3 via Inertia, e o sistema utiliza filas Redis para processamento assíncrono. O projeto está pronto para Docker e possui autenticação via Laravel Breeze.

## 📦 Requisitos
- PHP >= 8.2
- Composer
- Node.js >= 18
- Docker e Docker Compose (opcional)
- Redis
- Chave de API OpenAI ou Groq

## 🛠️ Tecnologias Utilizadas
- Laravel 12 (Backend)
- Inertia.js + Vue 3 (Frontend)
- TailwindCSS (Estilização)
- Laravel Breeze (Autenticação)
- Redis (Filas e Cache)
- Docker (Containerização)
- OpenAI ou Groq (Classificação IA)
- Laravel Reverb (WebSockets)

## 🚀 Instalação e Execução

### Local
```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# Configure sua chave OPENAI_KEY ou GROQ_KEY no .env
php artisan migrate --seed
docker-compose up --build # para o redis
php artisan serve
php artisan queue:work
php artisan reverb:start
```

## ⚙️ Funcionalidades
- Cadastro e autenticação de usuários
- Upload de arquivos CSV no formato:
  ```csv
  timestamp,domain,client_ip
  2025-08-18 10:32:00,facebook.com,192.168.0.10
  2025-08-18 10:32:02,example.xyz,192.168.0.20
  ```
- Processamento assíncrono dos logs via fila (Redis)
- Classificação automática dos domínios usando IA (OpenAI ou Groq)
- Visualização dos resultados classificados
- Políticas de acesso (DNSPolicy)
- Observadores (DnsObserver)
- Websockets

## 🔀 Rotas da Aplicação

### Usuários
- `GET /users` → Listar usuários
- `POST /users` → Criar usuário
- `PUT/PATCH /users/{id}` → Atualizar usuário
- `DELETE /users/{id}` → Remover usuário

### DNS Logs
- `GET /dns` → Listar logs classificados

### Upload de CSV
- `POST /upload` → Enviar arquivo CSV

## 🔄 Fluxo de Funcionamento

1. Usuário faz upload do CSV pela interface.
2. O arquivo é validado e armazenado.
3. O job `ImportCsvJob` importa os dados e cria registros DNS.
4. Para cada domínio, o job `ClassifyDomainJob` envia para classificação IA (OpenAiClassifier ou GroqAiClassifier).
5. O resultado (Seguro, Suspeito, Malicioso) é salvo e exibido na interface.

## 💡 Exemplo de Uso

1. Faça login/cadastro.
2. Acesse a página de upload e envie um arquivo CSV.
3. Aguarde o processamento (fila/worker).
4. Veja os resultados classificados na tela de logs DNS.

## 🐳 Dicas para Desenvolvimento

- Use Docker para facilitar o setup.
- Execute `php artisan queue:work` para processar jobs.
- Execute `php artisan reverb:start` para ativar o websocket.
- Configure corretamente sua chave OpenAI ou Groq no `.env`.
- Para testes, utilize arquivos CSV pequenos.
- Utilize o comando `composer run dev` para rodar tudo em paralelo (backend, fila, frontend).

## ❓ FAQ

- **Posso usar outra API de IA?** Sim, basta implementar a interface `AiClassifier` em `app/Services/Ai/`.
- **O processamento é instantâneo?** Não, depende do worker de filas estar rodando.
- **Como escalar?** Use múltiplos workers e configure Redis em modo cluster.
- **Como ver erros?** Consulte o log em `storage/logs/laravel.log`.

## 📝 Licença
Este projeto é apenas para fins de avaliação técnica.

