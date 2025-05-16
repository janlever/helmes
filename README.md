## Setup instructions
* Clone the repo or download and extract zip file
## Setting up database
1. Create a MySQL database with preferred name:
- on command line:
  * connect to mysql:
    
  ```
  mysql -u your_username -p
  ```
  * once connected to mysql, create database:
    
  ```
  CREATE DATABASE user_form;
  EXIT;
  ```   
2. Import database structure and data:
   `mysql -u username -p user_form < user_form_dump.sql`
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
