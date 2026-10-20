CREATE DATABASE IF NOT EXISTS studenthub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE studenthub;

CREATE TABLE IF NOT EXISTS students (
    student_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    course VARCHAR(10) NOT NULL,
    `year` TINYINT UNSIGNED NOT NULL,
    gender VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id),
    UNIQUE KEY uq_students_email (email),
    KEY idx_students_course (course)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
    event_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    PRIMARY KEY (event_id),
    KEY idx_events_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (registration_id),
    -- One student may register for a particular event only once.
    UNIQUE KEY uq_registrations_student_event (student_id, event_id),
    KEY idx_registrations_event (event_id),
    -- Removing a student or event also removes its dependent registrations.
    CONSTRAINT fk_registrations_student
        FOREIGN KEY (student_id) REFERENCES students (student_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_registrations_event
        FOREIGN KEY (event_id) REFERENCES events (event_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo-only values: these placeholders are not valid password hashes or logins.
INSERT INTO students (name, email, mobile, password_hash, course, `year`, gender)
SELECT 'Aarav Demo', 'aarav.demo@example.test', '9000000001',
       'DEMO_ONLY_NOT_A_LOGIN_HASH', 'CE', 2, 'Other'
WHERE NOT EXISTS (
    SELECT 1 FROM students WHERE email = 'aarav.demo@example.test'
);

INSERT INTO students (name, email, mobile, password_hash, course, `year`, gender)
SELECT 'Mira Sample', 'mira.sample@example.test', '9000000002',
       'DEMO_ONLY_NOT_A_LOGIN_HASH', 'IT', 3, 'Other'
WHERE NOT EXISTS (
    SELECT 1 FROM students WHERE email = 'mira.sample@example.test'
);

INSERT INTO events (title, description, event_date, venue)
SELECT 'Student Hub Orientation',
       'A fictional demonstration event for the Student Hub database.',
       '2027-01-15', 'Main Auditorium'
WHERE NOT EXISTS (
    SELECT 1 FROM events
    WHERE title = 'Student Hub Orientation'
      AND event_date = '2027-01-15'
      AND venue = 'Main Auditorium'
);

INSERT INTO events (title, description, event_date, venue)
SELECT 'Campus Coding Workshop',
       'A fictional demonstration workshop for student participants.',
       '2027-02-10', 'Computer Laboratory'
WHERE NOT EXISTS (
    SELECT 1 FROM events
    WHERE title = 'Campus Coding Workshop'
      AND event_date = '2027-02-10'
      AND venue = 'Computer Laboratory'
);

-- Link seed records by their stable email and event details, not generated IDs.
INSERT INTO registrations (student_id, event_id)
SELECT s.student_id, e.event_id
FROM students AS s
JOIN events AS e
  ON e.title = 'Student Hub Orientation'
 AND e.event_date = '2027-01-15'
 AND e.venue = 'Main Auditorium'
WHERE s.email = 'aarav.demo@example.test'
  AND NOT EXISTS (
      SELECT 1
      FROM registrations AS r
      WHERE r.student_id = s.student_id
        AND r.event_id = e.event_id
  );

INSERT INTO registrations (student_id, event_id)
SELECT s.student_id, e.event_id
FROM students AS s
JOIN events AS e
  ON e.title = 'Campus Coding Workshop'
 AND e.event_date = '2027-02-10'
 AND e.venue = 'Computer Laboratory'
WHERE s.email = 'mira.sample@example.test'
  AND NOT EXISTS (
      SELECT 1
      FROM registrations AS r
      WHERE r.student_id = s.student_id
        AND r.event_id = e.event_id
  );
