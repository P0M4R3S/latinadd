-- Inserta 30 posts alternando user_id 1 y 2
INSERT INTO posts (user_id, texto, tipo, fecha) VALUES
(1, 'Post de prueba 1 con imagen', 1, NOW()),
(2, 'Post de prueba 2 con imagen', 1, NOW()),
(1, 'Post de prueba 3 con imagen', 1, NOW()),
(2, 'Post de prueba 4 con imagen', 1, NOW()),
(1, 'Post de prueba 5 con imagen', 1, NOW()),
(2, 'Post de prueba 6 con imagen', 1, NOW()),
(1, 'Post de prueba 7 con imagen', 1, NOW()),
(2, 'Post de prueba 8 con imagen', 1, NOW()),
(1, 'Post de prueba 9 con imagen', 1, NOW()),
(2, 'Post de prueba 10 con imagen', 1, NOW()),
(1, 'Post de prueba 11 con imagen', 1, NOW()),
(2, 'Post de prueba 12 con imagen', 1, NOW()),
(1, 'Post de prueba 13 con imagen', 1, NOW()),
(2, 'Post de prueba 14 con imagen', 1, NOW()),
(1, 'Post de prueba 15 con imagen', 1, NOW()),
(2, 'Post de prueba 16 con imagen', 1, NOW()),
(1, 'Post de prueba 17 con imagen', 1, NOW()),
(2, 'Post de prueba 18 con imagen', 1, NOW()),
(1, 'Post de prueba 19 con imagen', 1, NOW()),
(2, 'Post de prueba 20 con imagen', 1, NOW()),
(1, 'Post de prueba 21 con imagen', 1, NOW()),
(2, 'Post de prueba 22 con imagen', 1, NOW()),
(1, 'Post de prueba 23 con imagen', 1, NOW()),
(2, 'Post de prueba 24 con imagen', 1, NOW()),
(1, 'Post de prueba 25 con imagen', 1, NOW()),
(2, 'Post de prueba 26 con imagen', 1, NOW()),
(1, 'Post de prueba 27 con imagen', 1, NOW()),
(2, 'Post de prueba 28 con imagen', 1, NOW()),
(1, 'Post de prueba 29 con imagen', 1, NOW()),
(2, 'Post de prueba 30 con imagen', 1, NOW());

-- Asume que los últimos 30 posts fueron los recién insertados
-- Si los IDs van del 101 al 130, por ejemplo, puedes ajustar esto
-- Aquí un ejemplo que los asigna desde 1 a 30 con 3 imágenes cada uno
INSERT INTO imagenes (post_id, ruta) VALUES
(1, 'img/posts/1.jpg'), (1, 'img/posts/2.jpg'), (1, 'img/posts/3.jpg'),
(2, 'img/posts/1.jpg'), (2, 'img/posts/2.jpg'), (2, 'img/posts/3.jpg'),
(3, 'img/posts/1.jpg'), (3, 'img/posts/2.jpg'), (3, 'img/posts/3.jpg'),
(4, 'img/posts/1.jpg'), (4, 'img/posts/2.jpg'), (4, 'img/posts/3.jpg'),
(5, 'img/posts/1.jpg'), (5, 'img/posts/2.jpg'), (5, 'img/posts/3.jpg'),
(6, 'img/posts/1.jpg'), (6, 'img/posts/2.jpg'), (6, 'img/posts/3.jpg'),
(7, 'img/posts/1.jpg'), (7, 'img/posts/2.jpg'), (7, 'img/posts/3.jpg'),
(8, 'img/posts/1.jpg'), (8, 'img/posts/2.jpg'), (8, 'img/posts/3.jpg'),
(9, 'img/posts/1.jpg'), (9, 'img/posts/2.jpg'), (9, 'img/posts/3.jpg'),
(10, 'img/posts/1.jpg'), (10, 'img/posts/2.jpg'), (10, 'img/posts/3.jpg'),
(11, 'img/posts/1.jpg'), (11, 'img/posts/2.jpg'), (11, 'img/posts/3.jpg'),
(12, 'img/posts/1.jpg'), (12, 'img/posts/2.jpg'), (12, 'img/posts/3.jpg'),
(13, 'img/posts/1.jpg'), (13, 'img/posts/2.jpg'), (13, 'img/posts/3.jpg'),
(14, 'img/posts/1.jpg'), (14, 'img/posts/2.jpg'), (14, 'img/posts/3.jpg'),
(15, 'img/posts/1.jpg'), (15, 'img/posts/2.jpg'), (15, 'img/posts/3.jpg'),
(16, 'img/posts/1.jpg'), (16, 'img/posts/2.jpg'), (16, 'img/posts/3.jpg'),
(17, 'img/posts/1.jpg'), (17, 'img/posts/2.jpg'), (17, 'img/posts/3.jpg'),
(18, 'img/posts/1.jpg'), (18, 'img/posts/2.jpg'), (18, 'img/posts/3.jpg'),
(19, 'img/posts/1.jpg'), (19, 'img/posts/2.jpg'), (19, 'img/posts/3.jpg'),
(20, 'img/posts/1.jpg'), (20, 'img/posts/2.jpg'), (20, 'img/posts/3.jpg'),
(21, 'img/posts/1.jpg'), (21, 'img/posts/2.jpg'), (21, 'img/posts/3.jpg'),
(22, 'img/posts/1.jpg'), (22, 'img/posts/2.jpg'), (22, 'img/posts/3.jpg'),
(23, 'img/posts/1.jpg'), (23, 'img/posts/2.jpg'), (23, 'img/posts/3.jpg'),
(24, 'img/posts/1.jpg'), (24, 'img/posts/2.jpg'), (24, 'img/posts/3.jpg'),
(25, 'img/posts/1.jpg'), (25, 'img/posts/2.jpg'), (25, 'img/posts/3.jpg'),
(26, 'img/posts/1.jpg'), (26, 'img/posts/2.jpg'), (26, 'img/posts/3.jpg'),
(27, 'img/posts/1.jpg'), (27, 'img/posts/2.jpg'), (27, 'img/posts/3.jpg'),
(28, 'img/posts/1.jpg'), (28, 'img/posts/2.jpg'), (28, 'img/posts/3.jpg'),
(29, 'img/posts/1.jpg'), (29, 'img/posts/2.jpg'), (29, 'img/posts/3.jpg'),
(30, 'img/posts/1.jpg'), (30, 'img/posts/2.jpg'), (30, 'img/posts/3.jpg');
