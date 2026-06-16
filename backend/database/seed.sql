USE eventilize;

-- =====================
-- USERS
-- =====================
INSERT INTO users (name,email,password_hash,role) VALUES
('Student One','student1@utm.my','hash123','student'),
('Student Two','student2@utm.my','hash123','student'),
('Computing Students Society','organizer@utm.my','hash123','organizer'),
('Organizer One','organizer1@utm.my','hash123','organizer'),
('Organizer Two','organizer2@utm.my','hash123','organizer'),
('Admin User','admin@eventilize.com','hash123','admin');

-- =====================
-- CATEGORIES
-- =====================
INSERT INTO categories (category_name) VALUES
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

-- =====================
-- EVENTS (8 EVENTS)
-- =====================
INSERT INTO events
(title,description,category_id,organizer_id,event_date,start_time,end_time,location,map_link,registration_link,event_image,status)
VALUES
('UTM Tech Talk 2026','AI and Future Tech talk session',2,3,'2026-06-10','10:00:00','12:00:00','DK1 UTM',NULL,NULL,NULL,'approved'),

('Career Fair 2026','Meet top companies hiring UTM students',8,4,'2026-06-15','09:00:00','17:00:00','UTM Arena',NULL,NULL,NULL,'approved'),

('Cultural Night','Celebrate multicultural performances',5,3,'2026-06-20','19:00:00','23:00:00','Dewan Sultan',NULL,NULL,NULL,'pending'),

('Sports Tournament','Interfaculty sports competition',4,4,'2026-06-25','08:00:00','18:00:00','Stadium UTM',NULL,NULL,NULL,'approved'),

('Research Seminar','Faculty research sharing session',10,3,'2026-07-01','09:00:00','12:00:00','FKE Seminar Room',NULL,NULL,NULL,'pending'),

('Hackathon Challenge','24-hour coding challenge',6,4,'2026-07-05','09:00:00','09:00:00','Innovation Lab',NULL,NULL,NULL,'approved'),

('Business Workshop','Entrepreneurship skills workshop',7,3,'2026-07-08','10:00:00','16:00:00','CTF Hall',NULL,NULL,NULL,'rejected'),

('UTM Gala Night 2026','Formal dinner & awards night',5,4,'2026-07-15','19:00:00','23:00:00','Grand Hall UTM','https://maps.google.com','https://forms.utm.my/gala',NULL,'approved');

-- =====================
-- BOOKMARKS
-- =====================
INSERT INTO bookmarks (user_id,event_id) VALUES
(1,1),
(1,2),
(2,4);

-- =====================
-- NOTIFICATIONS
-- =====================
INSERT INTO notifications (user_id,event_id,message) VALUES
(1,1,'Tech Talk has been approved'),
(2,2,'Career Fair is now live'),
(1,8,'Gala Night is confirmed');

-- =====================
-- ANALYTICS
-- =====================
INSERT INTO event_analytics (event_id,views_count,bookmarks_count) VALUES
(1,120,30),
(2,300,80),
(3,60,10),
(4,200,50),
(5,90,15),
(6,150,40),
(7,70,5),
(8,400,100);
