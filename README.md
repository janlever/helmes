## Setup instructions
1. Clone the repo or download and extract zip file
## Setting up database
1. Create a MySQL database with preferred name
2. Import database structure and data:
   `mysql -u username -p database_name < user_form_dump.sql`
   or use MySQL Workbench to import SQL file
3. Create .env file in project root and set your database credentials:
   ```
    servername=localhost
    username=your_username
    password=your_password
    database=user_form
   ```
## To start the app
1. in project root directory terminal, write:
   ```
   php -S localhost:8000
   ```
2. Navigate to the app URL
