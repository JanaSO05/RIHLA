# RIHLA | رحلة

An interactive, game-based website that teaches Saudi children aged 7–11
about Saudi heritage, history, and natural attractions.

Children choose between a land journey and a sea journey, explore
destinations (Diriyah, AlUla, Historic Jeddah, and the Red Sea), read short
cultural content, ask an AI Cultural Guide, take visual heritage quizzes
(shown to children as "challenges"), and collect points and badges in a
personal Explorer Notebook.

IT320 Practical Software Engineering — King Saud University
College of Computer and Information Sciences

## Technologies

- HTML, CSS, JavaScript (presentation)
- PHP (application logic)
- MySQL (data, through PDO)
- Google Gemini API (AI Cultural Guide)

## Project structure

rihla/
├── index.php, destination.php, landmark.php, quiz.php ...   Pages
├── views/       Header, footer, and the AI guide chat
├── logic/       Application logic (managers)
├── data/        Data access and the database connection (db.php)
├── assets/      CSS and images
└── rihla.sql    The database export

## Running the project locally

1. Install XAMPP or MAMP and start Apache and MySQL.
2. Copy the `rihla` folder into the `htdocs` directory.
3. In phpMyAdmin create a database named `rihla` with the collation
   `utf8mb4_unicode_ci`, open the `Import` tab, choose `rihla.sql`, and run it.
4. If your MySQL username or password is not `root`/`root`, change them at
   the top of `data/db.php`.
5. To use the AI Cultural Guide, copy `.env.example` to a new file named `.env`
   in the project folder and put your Gemini API key in it:
   `AI_API_KEY=your-key-here`.
   The `.env` file is excluded from GitHub by `.gitignore`, and `.htaccess`
   blocks it from being opened in the browser. Never write the key in the code
   or in the report.
6. Open `http://localhost/rihla`.

Test accounts: administrator `admin` / `admin123`, child `child` / `1234`.
A child can also create a new explorer profile from the login page.

## Sprint 1

Log in · Log out · Choose between land and sea journeys ·
Explore destinations · Search for destinations and landmarks ·
View destination and landmark content · Ask the AI Cultural Guide Chatbot ·
Manage destinations and landmarks · Add and update cultural content ·
Upload images

## Sprint 2

Create an explorer profile · View and edit the profile ·
Complete heritage quizzes · Points and badges ·
Save discoveries and progress in the Explorer Notebook · View the Explorer Notebook ·
Add and update quiz questions · Update badges and quiz points ·
Delete destinations, landmarks, content, quiz questions, and badges

## Team

See the AUTHORS file.
