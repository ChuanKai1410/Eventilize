USE eventilize;

INSERT INTO users (name, email, password_hash, role) VALUES
('Ahmad Student', 'student@utm.my', '$2y$10$zacANsK8yS6MZXfaTCe9ieMrQfgJQXsl7kY8SGmexjFfgypM9kAPy', 'student'),
('Computing Students Society', 'organizer@utm.my', '$2y$10$WFvBHWN8d6g8wYpF5zKDieasmbCYVdTAm3us0ztRkz1epR5ZcpCa6', 'organizer'),
('Super Admin', 'admin@utm.my', '$2y$10$zX.YdHW6qvjrl8AkNg2qzel3VLhM5CI9Wl3zKhEzYaCXhZLrZu1F6', 'admin')
ON DUPLICATE KEY UPDATE
name = VALUES(name),
password_hash = VALUES(password_hash),
role = VALUES(role);

INSERT IGNORE INTO categories (category_name) VALUES
('Technology'),
('Tech Talk'),
('Academic'),
('Sports'),
('Cultural'),
('Competition'),
('Workshop'),
('Career'),
('Faculty'),
('Seminar'),
('Residential');

DELETE n FROM notifications n
JOIN events e ON n.event_id = e.event_id
WHERE e.title IN ('UTM Tech Talk 2026', 'Career Fair 2026', 'Cultural Night', 'Sports Tournament', 'Research Seminar', 'Business Workshop');

DELETE b FROM bookmarks b
JOIN events e ON b.event_id = e.event_id
WHERE e.title IN ('UTM Tech Talk 2026', 'Career Fair 2026', 'Cultural Night', 'Sports Tournament', 'Research Seminar', 'Business Workshop');

DELETE er FROM event_registrations er
JOIN events e ON er.event_id = e.event_id
WHERE e.title IN ('UTM Tech Talk 2026', 'Career Fair 2026', 'Cultural Night', 'Sports Tournament', 'Research Seminar', 'Business Workshop');

DELETE ea FROM event_analytics ea
JOIN events e ON ea.event_id = e.event_id
WHERE e.title IN ('UTM Tech Talk 2026', 'Career Fair 2026', 'Cultural Night', 'Sports Tournament', 'Research Seminar', 'Business Workshop');

DELETE FROM events
WHERE title IN ('UTM Tech Talk 2026', 'Career Fair 2026', 'Cultural Night', 'Sports Tournament', 'Research Seminar', 'Business Workshop');

INSERT INTO events
(title, description, category_id, organizer_id, event_date, start_time, end_time, location, map_link, registration_link, event_image, poster_images, status, reject_reason, status_updated_at)
VALUES
('UTM Tech Talk 2026', 'A practical AI and future technology sharing session for UTM students.', (SELECT category_id FROM categories WHERE category_name = 'Tech Talk'), (SELECT user_id FROM users WHERE email = 'organizer@utm.my'), '2026-07-10', '10:00:00', '12:00:00', 'DK1, Faculty of Computing', 'https://maps.google.com', '', '', '[]', 'approved', NULL, NOW()),
('Career Fair 2026', 'Meet employers, explore internships, and prepare for graduate opportunities.', (SELECT category_id FROM categories WHERE category_name = 'Career'), (SELECT user_id FROM users WHERE email = 'organizer@utm.my'), '2026-07-18', '09:00:00', '17:00:00', 'UTM Arena', 'https://maps.google.com', 'https://forms.utm.my/career-fair', '', '[]', 'approved', NULL, NOW()),
('Cultural Night', 'A multicultural performance night celebrating student communities.', (SELECT category_id FROM categories WHERE category_name = 'Cultural'), (SELECT user_id FROM users WHERE email = 'organizer@utm.my'), '2026-07-25', '19:00:00', '23:00:00', 'Dewan Sultan Iskandar', 'https://maps.google.com', '', '', '[]', 'pending', NULL, NOW()),
('Sports Tournament', 'Interfaculty futsal and badminton tournament for campus teams.', (SELECT category_id FROM categories WHERE category_name = 'Sports'), (SELECT user_id FROM users WHERE email = 'organizer@utm.my'), '2026-08-03', '08:00:00', '18:00:00', 'UTM Sports Centre', 'https://maps.google.com', '', '', '[]', 'approved', NULL, NOW()),
('Research Seminar', 'Faculty research sharing session with postgraduate speakers.', (SELECT category_id FROM categories WHERE category_name = 'Seminar'), (SELECT user_id FROM users WHERE email = 'organizer@utm.my'), '2026-08-12', '09:00:00', '12:00:00', 'Seminar Room N28', 'https://maps.google.com', '', '', '[]', 'pending', NULL, NOW()),
('Business Workshop', 'Hands-on workshop for pitching and validating student startup ideas.', (SELECT category_id FROM categories WHERE category_name = 'Workshop'), (SELECT user_id FROM users WHERE email = 'organizer@utm.my'), '2026-08-20', '10:00:00', '16:00:00', 'Innovation Lab', 'https://maps.google.com', '', '', '[]', 'rejected', 'Please provide a clearer event agenda and target audience.', NOW());

INSERT INTO event_analytics (event_id, views_count, bookmarks_count)
SELECT event_id, 0, 0 FROM events
WHERE title IN ('UTM Tech Talk 2026', 'Career Fair 2026', 'Cultural Night', 'Sports Tournament', 'Research Seminar', 'Business Workshop')
ON DUPLICATE KEY UPDATE event_id = VALUES(event_id);

INSERT IGNORE INTO bookmarks (user_id, event_id)
SELECT u.user_id, e.event_id
FROM users u
JOIN events e ON e.title IN ('UTM Tech Talk 2026', 'Career Fair 2026')
WHERE u.email = 'student@utm.my';

UPDATE event_analytics ea
JOIN (
    SELECT event_id, COUNT(*) AS bookmark_total
    FROM bookmarks
    GROUP BY event_id
) b ON ea.event_id = b.event_id
SET ea.bookmarks_count = b.bookmark_total;

INSERT INTO event_registrations (user_id, event_id, status, registered_at, cancelled_at)
SELECT u.user_id, e.event_id, 'registered', NOW(), NULL
FROM users u
JOIN events e ON e.title = 'UTM Tech Talk 2026'
WHERE u.email = 'student@utm.my'
ON DUPLICATE KEY UPDATE status = 'registered', cancelled_at = NULL;

INSERT IGNORE INTO notification_settings (user_id)
SELECT user_id FROM users WHERE email = 'student@utm.my';

INSERT IGNORE INTO notifications (user_id, event_id, message, is_read)
SELECT u.user_id, e.event_id, 'Welcome to Eventilize. Sample events are ready to explore.', 0
FROM users u
LEFT JOIN events e ON e.title = 'UTM Tech Talk 2026'
WHERE u.email = 'student@utm.my';
