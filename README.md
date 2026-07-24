# Smart Mess / Tiffin Subscription System

This is a modern, responsive, and animated web application for a Smart Mess system. It uses HTML, CSS, JavaScript (Vanilla), PHP, and MySQL.

## Features
- **UI/UX**: Glassmorphism design, CSS animations, responsive layout.
- **Frontend**: Pure HTML/CSS/JS with AJAX for an SPA-like feel.
- **Backend**: Native PHP with Session based Authentication.
- **Database**: MySQL.

## Setup Instructions (XAMPP)

1. **Move Files**: Place the `smart_mess` folder inside your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\smart_mess`).
2. **Start Database**: Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Setup Database**:
   - Go to `http://localhost/phpmyadmin` in your browser.
   - You can just import the `database.sql` file or run its contents in the SQL tab. It will create `smart_mess` database and the tables.
   - Note: The database script inserts an admin user. 
     - **Email**: admin@smartmess.com
     - **Password**: password
4. **Configure DB**: If your MySQL password is not blank, edit `api/db.php` and update the `$password` variable.
5. **Run the App**: Go to `http://localhost/smart_mess` in your browser.

## Technologies Used
- HTML5, CSS3, ES6 JavaScript
- PHP 8+
- MySQL
