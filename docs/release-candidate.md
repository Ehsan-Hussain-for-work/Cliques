# Release Candidate Documentation (`cliques-lia`)

## 1. Release Candidate Identification

* **Repository:** `cliques-lia`
* **Commit Hash:** `8fdcc5f`
* **Branch / Tag:** `main`
* **Runtime Requirements:** PHP `^7.4 || ^8.0` with `ext-json` enabled, Composer 2.x

## 2. Installation

Clone the repository, check out the exact release candidate commit, and install dependencies:

```bash
git clone https://github.com/Ehsan-Hussain-for-work/Cliques cliques-lia
cd cliques-lia
git checkout 8fdcc5f
composer install --no-dev --optimize-autoloader
```

*(Note: Omit `--no-dev` if setting up a testing or staging environment.)*

## 3. Configuration
This application does not use a `.env` file. Configuration is split across two PHP files in `app/`:

1. **Application & Logging Settings (`app/settings.php`):**
   * `displayErrorDetails`: Currently `true` (must be changed to `false` for production).
   * `logError` & `logErrorDetails`: Currently `false` (set to `true` in production to capture runtime exceptions).
   * `logger.path`: Defaults to `logs/app.log` (automatically switches to `php://stdout` if the `docker` environment variable is set).
   * `logger.level`: Currently `Logger::DEBUG` (recommended: `Logger::WARNING` or `Logger::ERROR` in production).

2. **Database Connection (`app/dependencies.php`):**
   * Update the hardcoded `PDO::class` container definition to match the target MySQL instance:
     * `$host`: `'127.0.0.1'`
     * `$db`: `'cliques_db'`
     * `$user`: `'root'` *(replace with dedicated least-privilege DB user in production)*
     * `$pass`: `''` *(set strong production password)*
     * `$charset`: `'utf8mb4'`

3. **File Permissions:**
   * Ensure `logs/` exists and is writable by the web server user (`chmod -R 775 logs/`).

## 4. Schema, Migrations & Seed Data
* **Database Engine:** MySQL (`utf8mb4` character set) with PHP `ext-pdo` and `ext-pdo_mysql` enabled.
* **Database Name:** `cliques_db`
* **Schema & Migrations:** Import `cliques_db_sql.txt`: 
  ```bash
  mysql -u root -p cliques_db < cliques_db_sql.txt
  ```
  
* **Seed Data:** 
    ```bash
    mysql -u root -p cliques_db < cliques_dummy_values.sql 
    ```
## 5. Run & Verification Commands

### Local / Staging Execution

Start the built-in PHP development server (serves from `public/` on port 8080):

```bash
composer start
# Equivalent to: php -S localhost:8080 -t public
```
*(For production deployment, point your Nginx/Apache document root directly to the `public/` directory.)*

### Test Suite & Code Quality

Run the PHPUnit test suite (requires dev dependencies installed):

```bash
composer test
# Equivalent to: vendor/bin/phpunit
```

Optional static analysis & style checks:

```bash
vendor/bin/phpstan analyse
vendor/bin/phpcs
```

## 6. Deployment Readiness Checklist
- [ ] `composer install --no-dev --optimize-autoloader` completes cleanly
- [ ] PHP extensions verified (`ext-json`, `ext-pdo`, `ext-pdo_mysql`)
- [ ] `app/settings.php` updated for production (`displayErrorDetails => false`, error logging enabled)
- [ ] `app/dependencies.php` updated with target MySQL host, database (`cliques_db`), username, and password
- [ ] `cliques_db` database created and schema/seed SQL imported
- [ ] Web server document root points strictly to `/public` (`app/`, `src/`, `logs/`, and `vendor/` kept out of web root)
- [ ] `logs/app.log` directory permissions verified
- [ ] Smoke test endpoint returns HTTP `200` (and confirms database connectivity without triggering the `500 Database connection failed` fallback)
