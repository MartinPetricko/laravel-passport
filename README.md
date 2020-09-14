# Laravel Passport

### 1. Install passport
```bash
composer require laravel/passport
```

### 2. Migrate database
```bash
php artisan migrate
```

### 3. Create encryption keys
```bash
php artisan passport:install
```

Add `Client ID` and `Client Secret` to `.env`, `.env.example`.
```
PERSONAL_CLIENT_ID=1
PERSONAL_CLIENT_SECRET=2yAu4co...
PASSWORD_CLIENT_ID=2
PASSWORD_CLIENT_SECRET=eNpApDK...
```

Add to `config/auth.php`.
```
'personal_client_id' => env('PERSONAL_CLIENT_ID', '1'),
'personal_client_secret' => env('PERSONAL_CLIENT_SECRET', ''),
'password_client_id' => env('PASSWORD_CLIENT_ID', '2'),
'password_client_secret' => env('PASSWORD_CLIENT_SECRET', ''),
```

**Doesn't work on `php artisan serve`, because it uses only one procesor thread.**
