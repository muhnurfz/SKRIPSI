    CREATE DATABASE IF NOT EXISTS ticket_booking;
    USE ticket_booking;

 CREATE DATABASE IF NOT EXISTS ticket_booking;
USE ticket_booking;


CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `departure` varchar(50) DEFAULT NULL,
  `route` varchar(50) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `passenger_name` varchar(100) DEFAULT NULL,
  `passenger_phone` varchar(20) DEFAULT NULL,
  `booking_code` varchar(10) NOT NULL,
  `purchase_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `selected_seats` varchar(255) DEFAULT NULL,
  `uang_muka` int(11) DEFAULT NULL,
  `total_tariff` int(11) NOT NULL,
  `total_seats` int(11) NOT NULL DEFAULT '0',
  `check_in_status` tinyint(1) DEFAULT '0',
  `bus_code` varchar(20) DEFAULT NULL,
  `status_pembayaran` enum('verified','pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `bukti_pembayaran` longblob,
  `comments` text,
  `email` varchar(50) DEFAULT NULL,
  `tarif_id` int(11) DEFAULT NULL,
  `user_id_admin` int(11) DEFAULT NULL,
  `passenger_id` int(11) DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
  `pengajuan_batal` enum('tidak','ya') NOT NULL DEFAULT 'tidak'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Create the Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status_pembayaran ENUM('verified','pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    bukti_pembayaran LONGBLOB,
    comments TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
-- Ensure the Passengers table exists
CREATE TABLE IF NOT EXISTS passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(50) NOT NULL UNIQUE
);

-- Ensure the Routes table exists
CREATE TABLE IF NOT EXISTS routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    departure VARCHAR(50) NOT NULL,
    destination VARCHAR(50) NOT NULL
);

    CREATE TABLE IF NOT EXISTS booked_seats (
        booking_id INT,
        seat_number VARCHAR(5),
        PRIMARY KEY (booking_id, seat_number),
        FOREIGN KEY (booking_id) REFERENCES orders(id)
    );

DELIMITER //

CREATE EVENT IF NOT EXISTS delete_pending_orders
ON SCHEDULE EVERY 15 MINUTE
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM orders
    WHERE status_pembayaran = 'pending'
    AND purchase_date < NOW() - INTERVAL 2 HOUR;
END//

DELIMITER ;

CREATE TABLE `refund_payment` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `image` longblob NOT NULL,
  `upload_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

   
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE `users`
ADD `accessLevel` TINYINT NOT NULL;

UPDATE `users` SET `accessLevel` = 1 WHERE `username` = 'owner';
UPDATE `users` SET `accessLevel` = 2 WHERE `username` = 'pelayanan';
UPDATE `users` SET `accessLevel` = 2 WHERE `username` = 'berangkat';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'owner', MD5('owner123')),
(2, 'pelayanan', MD5('pelayanan123')),
(3, 'berangkat', MD5('berangkat123'));

--
-- Indexes for dumped tables

CREATE TABLE IF NOT EXISTS tarif_tiket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route VARCHAR(50) NOT NULL,
    tarif INT NOT NULL
);

INSERT INTO tarif_tiket (route, tarif) VALUES 
('PONOROGO', 250000),
('SOLO', 250000),
('BOJONEGORO', 210000),
('GEMOLONG', 210000);
