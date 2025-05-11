 Installation Guide
Follow these steps to set up and run the project locally:

✅ Requirements
Make sure you have the following installed:

PHP >= 8.0

Composer

Laravel >= 9.x

MySQL 


📦 Step-by-Step Installation
Clone the repository

git clone https://github.com/rakibch/todo-list-laravel.git
cd your-repository-name
Install dependencies

composer install
Copy the .env file and set your environment variables

cp .env.example .env

Generate application key

php artisan key:generate
Configure database

Edit the .env file and set your database credentials:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
Run database migrations and seeders

php artisan migrate --seed
(Optional) Install Laravel Sanctum for authentication

Sanctum is already included. If not, run:

composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
Serve the application

php artisan serve
Your API should now be accessible at: http://localhost:8000

🧪 Running Tests

php artisan test

📫 Authentication

POST /auth/register - Register a new user

POST /auth/login - Login and receive an access token

GET /auth/user - Fetch current authenticated user's information (requires auth)

Tasks (requires authentication)

POST /tasks - Create a new task

GET /tasks - List all tasks

GET /tasks/{task} - Show a specific task

PUT /tasks/{task} - Update an existing task

DELETE /tasks/{task} - Delete a task

POST /tasks/{task}/assign - Assign a user to a task

