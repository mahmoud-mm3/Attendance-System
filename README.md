\# School Attendance Management System



A Laravel-based web application for managing daily student attendance in schools, with role-based access control and reporting for administrators and academic supervisors.



\## Features



\- \*\*Attendance Recording\*\* — Teachers record attendance per class and session, with duplicate-entry prevention.

\- \*\*Role-Based Access Control\*\* — Three distinct roles with restricted access via custom middleware:

&#x20; - \*\*Admin\*\* — Full access: manages classes, subjects, students, teachers, and assignments.

&#x20; - \*\*School Recorder (الإداري)\*\* — Records attendance and views reports.

&#x20; - \*\*Academic Supervisor (المشرف التربوي)\*\* — Views reports only.

\- \*\*CRUD Management\*\* — Classes, subjects, students, teachers, and teacher-subject assignments.

\- \*\*Reporting\*\* — Per-student and per-class attendance statistics using aggregate queries.

\- \*\*Performance-Conscious Design\*\* — Eager loading to avoid N+1 queries, pagination for large student lists, and a composite database index on attendance records.



\## Tech Stack



\- \*\*Backend:\*\* PHP, Laravel

\- \*\*Database:\*\* MySQL

\- \*\*ORM:\*\* Eloquent

\- \*\*Templating:\*\* Blade



\## Getting Started (Local Setup)



1\. Clone the repository:

```bash

&#x20;  git clone https://github.com/mahmoud-mm3/Attendance-System.git

&#x20;  cd Attendance-System

```

2\. Install dependencies:

```bash

&#x20;  composer install

```

3\. Copy the environment file and generate an app key:

```bash

&#x20;  cp .env.example .env

&#x20;  php artisan key:generate

```

4\. Set your database credentials in `.env`, then run migrations:

```bash

&#x20;  php artisan migrate

```

5\. Serve the application:

```bash

&#x20;  php artisan serve

```



\## Author



\*\*Mahmoud Mohamed Alaa\*\*

\[GitHub](https://github.com/mahmoud-mm3) · \[LinkedIn](https://www.linkedin.com/in/mahmoud-mohammed-45364a326/)

