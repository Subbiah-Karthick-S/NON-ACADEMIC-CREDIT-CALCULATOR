# Non Academic Credit Calculator

The **Non Academic Credit Calculator** is a web application designed to help academic institutions manage and track non-academic credits for students. This system enables students to upload certificates of participation in non-academic activities, which are verified by staff members, and credits are awarded accordingly. The application includes user-friendly interfaces for students and staff, along with automated mentor-student relationship management.

---

## Features

### Students
- **Account Creation**: Students can create accounts by providing details like name, email, department, year, section, mentor, register number, password, and a profile photo.
- **Login & Profile Management**: Students can log in, view their profile, and update information securely.
- **Certificate Upload**: Students can upload certificates for various non-academic activities.
- **View Credit Points**: Students can see the total credits awarded for their activities.

### Staff
- **Account Creation**: Staff members can create accounts by providing details like name, email, department, password, and a profile photo.
- **Login & Profile Management**: Staff can log in and manage their profiles.
- **Certificate Validation**: Staff members validate certificates uploaded by students and award appropriate credits.
- **View Student Credits**: Staff can view the credit points of students under their mentorship.

### System Features
- **Mentor-Student Relationship Management**: Relationships between students and mentors are automatically managed through triggers.
- **Secure Authentication**: Passwords are hashed for secure storage.
- **Automated Data Handling**: Triggers ensure smooth data synchronization between tables.
- **Responsive Design**: The system is built with a responsive UI for seamless usage on various devices.

---

## Database Schema

### Tables
1. **students**:
   - Stores student details such as name, email, department, year, section, mentor, register number, password, and profile photo.

2. **staff**:
   - Stores staff details such as name, email, department, password, and profile photo.

3. **certificates**:
   - Tracks certificates uploaded by students, their types, upload dates, file names, and credits awarded.

4. **mentor_student**:
   - Manages mentor-student relationships by linking students' register numbers to mentors' names.

### Triggers
1. **after_student_insert**:
   - Automatically creates mentor-student relationships when a new student is added.

---

## Installation

1. **Setup Environment**:
   - Install a local server like WAMP, XAMPP, or MAMP.

2. **Import Database**:
   - Import the `database.sql` file into your MySQL database to create tables and triggers.

3. **Configure Database**:
   - Update database connection details (`servername`, `username`, `password`, `dbname`) in all `.php` files.

4. **Start the Server**:
   - Place the project files in the `www` or `htdocs` directory.
   - Start the local server and access the project via your browser.

---

## Usage

1. **Student Workflow**:
   - Create an account and log in.
   - Upload certificates for validation.
   - View awarded credits and notifications.

2. **Staff Workflow**:
   - Create an account and log in.
   - Validate student certificates.
   - Award credits and view student credit points.

---

## Technologies Used

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Server**: WAMP/XAMPP

---

## Future Enhancements

- Integrating email notifications for updates.
- Adding analytics to visualize student performance.
- Supporting bulk certificate uploads and validations.

---

## Copyright

© 2024 Subbiah Karthick. All rights reserved.

---

## License

This project is not currently licensed. If you would like to use this project, please contact me for permissions.

---

## Contact

For any questions or suggestions, feel free to reach out:

- **Email**: [subbiahkarthickcse@gmail.com](mailto:subbiahkarthickcse@gmail.com)  
- **GitHub**: [Subbiah-Karthick-S](https://github.com/Subbiah-Karthick-S)


