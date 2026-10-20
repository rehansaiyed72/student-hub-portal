# Practical 8 — MySQL Schema Design, ER Model, PDO Connectivity, and Prepared Statements

## Aim

Design a normalized MySQL database for StudentHub and demonstrate secure PHP
database connectivity and parameterized queries using PDO.

## Software and tools used

- PHP 8.5 with PDO and the `pdo_mysql` extension
- MySQL Server and the MySQL command-line client
- Visual Studio Code and a web browser

## Database schema overview

The `studenthub` database contains `students`, `events`, and `registrations`.
Each table has an auto-increment primary key. Student email is unique.
Registrations contain foreign keys to a student and an event, with a unique
student/event pair to prevent duplicates. The tables use InnoDB and `utf8mb4`.

## ER relationship

One student can have many registrations, and one event can have many
registrations. Therefore students and events have a many-to-many relationship,
implemented by the `registrations` junction table. See [er-model.md](./er-model.md)
for the ER diagram.

## Normalization

Student attributes are stored once in `students`, and event attributes are
stored once in `events`. `registrations` stores references and registration
time instead of repeating names, contact information, or event descriptions.
This separation reduces update anomalies and duplicate data.

## PDO connection

`php/db.php` connects to MySQL at `127.0.0.1` and selects `studenthub` using
`utf8mb4`. It obtains the username and password from the
`STUDENTHUB_DB_USER` and `STUDENTHUB_DB_PASSWORD` environment variables. It
enables exception-based errors, associative fetches, and native prepared
statements. Connection details are logged server-side; pages show generic
failure messages.

## Prepared statements

`php/test_students.php` validates the requested course against the existing
course codes (`CE`, `IT`, `CSE`, and `AI`) and binds it to a PDO parameter.
`php/test_events.php` also binds a parameter when retrieving event records.
Neither query concatenates user input into SQL. Displayed database values are
escaped with `htmlspecialchars()`.

## Error handling and security

- SQL uses primary keys, foreign keys, unique constraints, and indexes.
- PDO throws exceptions; connection and query details are written to the PHP
  error log rather than browser output.
- Browser pages show generic error messages.
- User-controlled course input is allowlisted and bound as a parameter.
- Displayed database content is HTML-escaped.
- Seed password values are demonstration-only placeholders, not valid hashes
  or login credentials.
- The Practical 7 registration form continues to save to CSV. These database
  examples do not change that form.

## Execution steps

1. In a VS Code PowerShell terminal, import the schema. The MySQL client prompts
   for the existing MySQL password; do not put it in the command:

   ```powershell
   & 'C:\Program Files\MySQL\MySQL Server 26.10\bin\mysql.exe' -u root -p --execute='source C:/Users/rehan/Documents/wdf/PRAC1WDF/sql/studenthub.sql'
   ```

2. Set the database credentials for the PHP development server. The secure
   credential prompt avoids putting the password in the command history:

   ```powershell
   $credential = Get-Credential -UserName root -Message 'Enter the MySQL password for StudentHub'
   $env:STUDENTHUB_DB_USER = $credential.UserName
   $env:STUDENTHUB_DB_PASSWORD = $credential.GetNetworkCredential().Password
   ```

3. From the project root, start PHP's built-in server in the same terminal so
   it inherits those environment variables:

   ```powershell
   & 'C:\php\php-8.5.11-Win32-vs17-x64\php.exe' -S 127.0.0.1:8000
   ```

4. Open the test pages in a browser:

   - `http://127.0.0.1:8000/php/test_connection.php`
   - `http://127.0.0.1:8000/php/test_students.php?course=CE`
   - `http://127.0.0.1:8000/php/test_events.php`

5. Stop the server with `Ctrl+C`. Close the terminal when finished to clear
   its environment variables.

## Expected results

After the SQL script is imported and valid credentials are configured, the
connection test reports the selected database name. The student page shows
students for the selected allowed course, and the event page lists the seeded
events. If setup or a query fails, the pages show a generic error message.

## Conclusion

This practical demonstrates a relational schema with referential integrity,
the many-to-many ER relationship, PDO connectivity, and prepared statements.
It keeps the existing CSV-based Practical 7 registration workflow unchanged.
