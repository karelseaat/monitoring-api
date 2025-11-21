# Multi-Tenant SaaS API for a Monitoring Service

This project is the core backend for a Software-as-a-Service (SaaS) platform where companies can monitor the uptime and performance of their websites. It's built with Lumen and designed to handle multiple isolated customer organizations from a single codebase.

## Features

The Monitoring Service API provides the following key functionalities:

*   **Multi-tenancy Architecture:** Supports multiple, isolated customer organizations with strict data partitioning.
*   **User Authentication & Authorization:**
    *   User registration and login using API tokens.
    *   Role-Based Access Control (RBAC) with 'admin' and 'member' roles, controlling access to monitor management.
*   **Monitor Management:** Full CRUD (Create, Read, Update, Delete) operations for website monitors.
*   **Automated Monitor Checks:**
    *   Scheduled Artisan command (`monitor:check`) to dispatch jobs for active monitors.
    *   Asynchronous monitor checks processed via a queue worker.
*   **Basic Alerting Mechanism:** Logs warning messages when monitors go down or time out, and info messages when they come back up.
*   **Historical Check Results:** API endpoints to retrieve past check results for any monitor, with filtering and pagination.
*   **Aggregated Statistics:** API endpoints to provide performance analytics for monitors, such as uptime percentage and average response time over various periods.

## Requirements

*   PHP (>= 8.1)
*   Composer
*   SQLite (or any other database supported by Laravel/Lumen)

## Setup Instructions

1.  **Clone the Repository (or create Lumen project):**
    If you haven't already, create a new Lumen project:
    ```bash
    composer create-project --prefer-dist laravel/lumen monitoring-api
    cd monitoring-api
    ```

2.  **Configure Environment:**
    Create a `.env` file from the example:
    ```bash
    cp .env.example .env
    ```
    Edit your `.env` file. Configure it for SQLite and update the `DB_DATABASE` path to an absolute path on your system. Also, set `QUEUE_CONNECTION` to `database`. For example:
    ```
    DB_CONNECTION=sqlite
    DB_DATABASE=/path/to/your/project/database/database.sqlite
    DB_FOREIGN_KEYS=true

    QUEUE_CONNECTION=database
    ```
    Ensure Eloquent, facades, AppServiceProvider, and AuthServiceProvider are enabled in `bootstrap/app.php`:
    ```php
    // In bootstrap/app.php
    $app->withFacades();
    $app->withEloquent();
    $app->register(App\Providers\AppServiceProvider::class);
    $app->register(App\Providers\AuthServiceProvider::class);
    $app->configure('queue'); // Ensure queue config is loaded
    ```

3.  **Create Database File:**
    ```bash
    touch database/database.sqlite
    ```

4.  **Install Dependencies:**
    ```bash
    composer install
    composer require guzzlehttp/guzzle
    ```

5.  **Run Migrations:**
    Create the necessary database tables (including `jobs` and `failed_jobs` for the queue):
    ```bash
    php artisan migrate
    ```

## Running the Application

To start the Lumen development server, run the following command from the project root:

```bash
php -S localhost:8000 -t public
```

The API will be available at `http://localhost:8000`.

### Running Queue Worker and Scheduler

For monitor checks to run automatically, you need to:

1.  **Start the Queue Worker:** In a separate terminal window (from the project root):
    ```bash
    php artisan queue:work
    ```
2.  **Configure Scheduler (Crontab):** Add the following entry to your server's crontab to run Lumen's scheduler every minute:
    ```bash
    * * * * * cd /path/to/your/project/monitoring-api && php artisan schedule:run >> /dev/null 2>&1
    ```
    (Replace `/path/to/your/project/monitoring-api` with the actual absolute path to your project.)

## API Endpoints

Below is a summary of the available API endpoints. Replace `http://localhost:8000` with your server's address.

### Authentication Endpoints (Public):

*   **`POST /register`**
    *   Registers a new user and creates an organization. First user is 'admin'.
    *   Request Body: `{ "organization_name": "...", "name": "...", "email": "...", "password": "..." }`
*   **`POST /login`**
    *   Authenticates a user and returns a new API token.
    *   Request Body: `{ "email": "...", "password": "..." }`

### Monitor Endpoints (Authenticated - requires `Authorization: Bearer YOUR_API_TOKEN` header or `?api_token=YOUR_API_TOKEN` query param):

*   **`GET /monitors`**
    *   List all monitors for the authenticated organization.
*   **`POST /monitors`**
    *   Create a new monitor (requires 'admin' role).
    *   Request Body: `{ "name": "...", "url": "...", "check_interval": (minutes) }`
*   **`GET /monitors/{id}`**
    *   Retrieve a specific monitor by ID.
*   **`PUT /monitors/{id}`**
    *   Update an existing monitor (requires 'admin' role).
    *   Request Body: `{ "name": "...", "url": "...", "check_interval": ..., "status": "active|paused" }`
*   **`DELETE /monitors/{id}`**
    *   Delete a monitor by ID (requires 'admin' role).
*   **`GET /monitors/{id}/checks?start_date={...}&end_date={...}&limit={num?}&offset={num?}`**
    *   Get historical check results for a monitor.
*   **`GET /monitors/{id}/statistics?period={24h|7d|30d|90d?}`**
    *   Get aggregated performance statistics for a monitor.

---