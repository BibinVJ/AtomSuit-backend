# Pharmacy Manager backend api
This is an **API-only** backend for **Pharmacy Manager**.


## Requirements
- PHP 8.1+
- MySQL
- Composer
- **Supervisor** (for managing queue workers in production)


yml commands to run
php artisan optimize:clear
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan queue:restart


## Developer Setup (Local)
```bash
- cp .env.example .env
- composer install

- php artisan key:generate

- php artisan migrate --seed
- php artisan db:seed --class=UsersSeeder # (Optional) Create default users for each role if needed

- php artisan passport:keys --force  # Generates Passport keys
- php artisan passport:client --personal  # Generates a personal access client

- php artisan queue:listen
```


## Social Login Note
If you're using social login, make sure the **frontend URL** is correctly set in the `.env` file:

```env
FRONTEND_URL=http://localhost:3000
```

This ensures proper redirection after login.
