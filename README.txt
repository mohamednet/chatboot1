IPTV Support Chatbot - Installation and Setup Guide
===========================================

This is an IPTV support chatbot built with Laravel, integrated with Facebook Messenger and OpenAI's GPT for handling customer inquiries.

Prerequisites
------------
1. PHP 8.1 or higher
2. Composer
3. MySQL
4. XAMPP/Apache server
5. Facebook Developer Account
6. OpenAI API Key

Initial Setup
------------
1. Clone the repository to your local machine
2. Navigate to project directory
3. Run: composer install

Environment Configuration
------------------------
1. Copy .env.example to .env
2. Configure the following in .env:

   # Database Configuration
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password

   # Facebook Configuration
   FACEBOOK_PAGE_ACCESS_TOKEN="your_facebook_page_token"
   FACEBOOK_VERIFY_TOKEN="your_verify_token"

   # OpenAI Configuration
   OPENAI_API_KEY="your_openai_api_key"

   # Queue Configuration
   QUEUE_CONNECTION=database

Database Setup
-------------
1. Create a new MySQL database
2. Run migrations:
   php artisan migrate:fresh

Clear Application Cache
----------------------
Run these commands in order:
1. php artisan config:clear
2. php artisan cache:clear
3. php artisan route:clear
4. php artisan view:clear

Starting the Application
-----------------------
You need TWO separate terminal windows:

Terminal 1 (Queue Worker):
1. Navigate to project directory
2. Run: php artisan queue:work --verbose
3. Keep this terminal running

Terminal 2 (Laravel Server):
1. Navigate to project directory
2. Run: php artisan serve
3. Keep this terminal running

The application should now be running at http://127.0.0.1:8000

Facebook Webhook Setup
---------------------
1. Go to Facebook Developer Console
2. Set up a new webhook
3. Webhook URL: your-domain/webhook
4. Verify Token: Same as FACEBOOK_VERIFY_TOKEN in .env
5. Subscribe to messages and messaging_postbacks

Troubleshooting
--------------
If you encounter issues:

1. Queue Worker Stops Working:
   - Run: php artisan queue:restart
   - Then: php artisan queue:work --verbose

2. Database Issues:
   - Check database connection in .env
   - Run: php artisan migrate:fresh

3. Configuration Issues:
   - Run all cache clear commands mentioned above
   - Verify .env values are correct

4. Facebook Webhook Issues:
   - Verify tokens match in Facebook Developer Console and .env
   - Check webhook URL is accessible
   - Verify SSL certificate if using HTTPS

Important Notes
--------------
1. ALWAYS keep both terminals running (queue worker and Laravel server)
2. Monitor storage/logs/laravel.log for errors
3. After code changes:
   - Run: php artisan queue:restart
   - Restart queue worker

Features
--------
1. Message Batching:
   - Collects messages for 10 seconds
   - Combines multiple messages into one context
   - Provides coherent responses

2. Error Handling:
   - Automatic job retries (3 attempts)
   - Detailed error logging
   - Failed job tracking

3. Support for:
   - Text messages
   - Image messages
   - Quick replies

Maintenance
-----------
Regular maintenance tasks:

1. Monitor log files:
   - Check storage/logs/laravel.log
   - Clear logs periodically

2. Database maintenance:
   - Regular backups
   - Clear old messages periodically

3. Queue maintenance:
   - Monitor failed_jobs table
   - Clear old jobs periodically

For Support
----------
If you encounter any issues:
1. Check the logs in storage/logs/laravel.log
2. Verify all services are running
3. Ensure all environment variables are set correctly

Last Updated: 2024-12-19
