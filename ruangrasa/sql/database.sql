-- Ruang Rasa Database Schema
-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS ruangrasa;
CREATE DATABASE ruangrasa;
USE ruangrasa;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto VARCHAR(255) DEFAULT 'default.png',
    role ENUM('admin', 'user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: kategori
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: menu
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    id_kategori INT NOT NULL,
    gambar VARCHAR(255) DEFAULT 'default_menu.png',
    deskripsi TEXT,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: pesanan
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nomor_meja INT NOT NULL,
    total_harga INT NOT NULL,
    status ENUM('pending', 'dimasak', 'selesai') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin Account (password: admin123)
INSERT INTO users (nama, email, password, foto, role) VALUES
('Administrator', 'admin@ruangrasa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 'admin');

-- Seed Categories
INSERT INTO kategori (nama_kategori) VALUES
('Makanan'),
('Minuman'),
('Camilan'),
('Paket Combo');

-- Seed Menu Items with Rich Indonesian Culinary Descriptions
INSERT INTO menu (nama_menu, harga, id_kategori, gambar, deskripsi) VALUES
-- Makanan (id_kategori = 1)
('Ayam Cabe Ijo', 35000, 1, 'default_menu.png', 'Potongan ayam kampung pilihan yang dimasak dengan cabai hijau segar, bumbu rempah tradisional Padang yang kaya akan cita rasa pedas gurih. Cocok disantap dengan nasi putih hangat dan sambal lado mudo untuk sensasi pedas yang menggugah selera.'),
('Gulai Otak', 28000, 1, 'default_menu.png', 'Otak sapi segar yang diolah dengan kuah gulai santan kental, bumbu kunyit, lengkuas, dan serai. Tekstur lembut berpadu dengan aroma rempah yang harum, menciptakan hidangan otentik khas Minangkabau yang menggoda.'),
('Gulai Cincang', 32000, 1, 'default_menu.png', 'Daging sapi cincang premium dimasak dalam kuah gulai santan gurih dengan tambahan kentang, wortel, dan bumbu rempah lengkap. Hidangan ini memiliki cita rasa kaya dan tekstur yang lembut, sangat cocok untuk santap siang atau malam.'),
('Telur Balado', 15000, 1, 'default_menu.png', 'Telur rebus yang digoreng garing kemudian disiram dengan sambal balado merah menyala dari cabai merah keriting, bawang merah, dan tomat segar. Kombinasi pedas manis yang sempurna untuk pelengkap hidangan nasi padang Anda.'),
('Jangek Siram', 38000, 1, 'default_menu.png', 'Kulit sapi pilihan yang direbus hingga empuk, dipotong tipis, lalu disiram dengan kuah gulai kental bersantan dengan bumbu kari yang kaya rasa. Tekstur kenyal dan kuah yang creamy menjadikan hidangan ini favorit pecinta kuliner tradisional.'),
('Paru Sapi Goreng', 30000, 1, 'default_menu.png', 'Paru sapi segar yang dibersihkan sempurna, direbus dengan bumbu rempah, kemudian digoreng kering hingga garing di luar namun tetap lembut di dalam. Disajikan dengan sambal lado dan irisan cabe rawit untuk pengalaman rasa yang otentik.'),
('Udang Balado', 45000, 1, 'default_menu.png', 'Udang segar berukuran jumbo yang digoreng crispy lalu dilumuri sambal balado pedas manis khas Padang. Perpaduan rasa pedas, manis, dan gurihnya udang menciptakan harmoni cita rasa yang sulit dilupakan.'),
('Rendang Daging Sapi', 50000, 1, 'default_menu.png', 'Daging sapi empuk yang dimasak berjam-jam dengan santan kental, cabai merah, bawang, jahe, lengkuas, kunyit, dan serai hingga bumbu meresap sempurna. Hidangan legendaris Indonesia dengan kelembutan daging dan rempah yang kaya.'),
('Dendeng Balado', 42000, 1, 'default_menu.png', 'Irisan tipis daging sapi yang dikeringkan dan digoreng crispy, kemudian dibaluri sambal balado pedas yang menggoda. Tekstur renyah berpadu rasa pedas manis menciptakan sensasi unik di lidah.'),
('Sate Padang', 40000, 1, 'default_menu.png', 'Sate daging sapi empuk yang dipanggang sempurna dengan bumbu kacang kental berwarna kuning kecoklatan, diperkaya dengan kuah gulai khas Padang. Disajikan dengan lontong dan taburan bawang goreng.'),

-- Minuman (id_kategori = 2)
('Es Campur', 18000, 2, 'default_menu.png', 'Minuman segar berbagai macam buah tropis seperti alpukat, nangka, kelapa muda, kolang-kaling, dan agar-agar, disiram dengan sirup manis dan susu kental, ditaburi es serut. Sempurna untuk menghilangkan dahaga di siang hari.'),
('Es Jeruk Segar', 12000, 2, 'default_menu.png', 'Perasan jeruk manis segar tanpa tambahan pengawet, dicampur dengan air dingin dan es batu. Rasa asam manis alami yang menyegarkan tenggorokan dan menambah semangat.'),
('Es Teh Manis', 8000, 2, 'default_menu.png', 'Teh hitam pilihan yang diseduh sempurna, diberi gula secukupnya dan es batu. Minuman klasik Indonesia yang cocok menemani segala jenis hidangan.'),
('Jus Alpukat', 20000, 2, 'default_menu.png', 'Alpukat segar pilihan yang diblender halus dengan susu, gula, dan es batu hingga creamy. Kaya akan nutrisi dan memiliki tekstur lembut yang memanjakan lidah.'),
('Es Kelapa Muda', 15000, 2, 'default_menu.png', 'Air kelapa muda segar langsung dari buahnya, disajikan dingin dengan potongan daging kelapa yang lembut. Minuman alami penuh elektrolit yang menyegarkan dan menyehatkan.');
