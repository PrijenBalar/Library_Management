-- =====================================================================
--  Online Library Management System - Database
--  Database name : library_db
--  Import this file using phpMyAdmin (http://localhost/phpmyadmin)
--  See "read me/installation.txt" for step by step instructions.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `library_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `library_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `tblorderitems`;
DROP TABLE IF EXISTS `tblorders`;
DROP TABLE IF EXISTS `tblcart`;
DROP TABLE IF EXISTS `tblissuedbookdetails`;
DROP TABLE IF EXISTS `tblbooks`;
DROP TABLE IF EXISTS `tblauthors`;
DROP TABLE IF EXISTS `tblcategory`;
DROP TABLE IF EXISTS `tblstudents`;
DROP TABLE IF EXISTS `admin`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Table: admin
-- ---------------------------------------------------------------------
CREATE TABLE `admin` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `FullName` VARCHAR(100) NOT NULL,
  `AdminEmail` VARCHAR(120) DEFAULT NULL,
  `UserName` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `updationDate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UserName` (`UserName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin  ->  username: admin   password: Password@123
INSERT INTO `admin` (`id`, `FullName`, `AdminEmail`, `UserName`, `Password`) VALUES
(1, 'Administrator', 'admin@library.com', 'admin', '$2y$10$S6a9fbQ21i7zWWmbk9EKou4ZPaALltXHtzUOssrIZzwdcTZHtz98W');

-- ---------------------------------------------------------------------
-- Table: tblcategory
-- ---------------------------------------------------------------------
CREATE TABLE `tblcategory` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `CategoryName` VARCHAR(150) NOT NULL,
  `Status` TINYINT(1) NOT NULL DEFAULT 1,
  `CreationDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdationDate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblcategory` (`id`, `CategoryName`, `Status`) VALUES
(1, 'Computer Science', 1),
(2, 'Mathematics', 1),
(3, 'Physics', 1),
(4, 'Literature', 1),
(5, 'History', 1),
(6, 'Management', 1);

-- ---------------------------------------------------------------------
-- Table: tblauthors
-- ---------------------------------------------------------------------
CREATE TABLE `tblauthors` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `AuthorName` VARCHAR(150) NOT NULL,
  `CreationDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdationDate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tblauthors` (`id`, `AuthorName`) VALUES
(1, 'Robert C. Martin'),
(2, 'Thomas H. Cormen'),
(3, 'Abraham Silberschatz'),
(4, 'R.D. Sharma'),
(5, 'H.C. Verma'),
(6, 'William Shakespeare'),
(7, 'Yuval Noah Harari'),
(8, 'Peter F. Drucker'),
(9, 'E. Balagurusamy'),
(10, 'Andrew S. Tanenbaum');

-- ---------------------------------------------------------------------
-- Table: tblbooks
-- ---------------------------------------------------------------------
CREATE TABLE `tblbooks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `BookName` VARCHAR(200) NOT NULL,
  `CatId` INT NOT NULL,
  `AuthorId` INT NOT NULL,
  `ISBNNumber` VARCHAR(30) NOT NULL,
  `BookPrice` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `SellStock` INT NOT NULL DEFAULT 0 COMMENT 'Copies available for sale',
  `BookImage` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Cover image file in uploads/books/',
  `RegDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdationDate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ISBNNumber` (`ISBNNumber`),
  KEY `CatId` (`CatId`),
  KEY `AuthorId` (`AuthorId`),
  CONSTRAINT `fk_books_category` FOREIGN KEY (`CatId`) REFERENCES `tblcategory` (`id`),
  CONSTRAINT `fk_books_author` FOREIGN KEY (`AuthorId`) REFERENCES `tblauthors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SellStock = number of copies available to BUY (book selling module).
-- It is separate from the library copy that is issued / returned.
INSERT INTO `tblbooks` (`id`, `BookName`, `CatId`, `AuthorId`, `ISBNNumber`, `BookPrice`, `SellStock`, `BookImage`) VALUES
(1, 'Clean Code', 1, 1, '9780132350884', 450.00, 8, '9780132350884.jpg'),
(2, 'Introduction to Algorithms', 1, 2, '9780262033848', 850.00, 4, '9780262033848.jpg'),
(3, 'Database System Concepts', 1, 3, '9780073523323', 700.00, 5, '9780073523323.jpg'),
(4, 'Operating System Concepts', 1, 3, '9781118063330', 650.00, 6, '9781118063330.jpg'),
(5, 'Computer Networks', 1, 10, '9780132126953', 620.00, 3, '9780132126953.jpg'),
(6, 'Programming in ANSI C', 1, 9, '9789339219666', 350.00, 10, '9789339219666.jpg'),
(7, 'Mathematics Class 12', 2, 4, '9789383182480', 550.00, 7, '9789383182480.jpg'),
(8, 'Concepts of Physics Vol 1', 3, 5, '9788177091878', 400.00, 5, '9788177091878.jpg'),
(9, 'Hamlet', 4, 6, '9780743477123', 250.00, 2, '9780743477123.jpg'),
(10, 'Sapiens: A Brief History of Humankind', 5, 7, '9780062316097', 499.00, 6, '9780062316097.jpg'),
(11, 'The Effective Executive', 6, 8, '9780060833459', 380.00, 0, '9780060833459.jpg'),
(12, 'Clean Architecture', 1, 1, '9780134494166', 520.00, 4, '9780134494166.jpg');

-- ---------------------------------------------------------------------
-- Table: tblstudents
-- ---------------------------------------------------------------------
CREATE TABLE `tblstudents` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `StudentId` VARCHAR(20) NOT NULL,
  `FullName` VARCHAR(120) NOT NULL,
  `EmailId` VARCHAR(120) NOT NULL,
  `MobileNumber` VARCHAR(15) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `Status` TINYINT(1) NOT NULL DEFAULT 1,
  `RegDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdationDate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `StudentId` (`StudentId`),
  UNIQUE KEY `EmailId` (`EmailId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample students  ->  password for all: Student@123
INSERT INTO `tblstudents` (`id`, `StudentId`, `FullName`, `EmailId`, `MobileNumber`, `Password`, `Status`) VALUES
(1, 'SID10001', 'Rahul Sharma', 'rahul@example.com', '9876543210', '$2y$10$GLmeJYmmgFEz/xexN/5KYeOcifxK7WsPeRe3I3CUeOTsZTE/ExoOy', 1),
(2, 'SID10002', 'Priya Patel', 'priya@example.com', '9123456780', '$2y$10$GLmeJYmmgFEz/xexN/5KYeOcifxK7WsPeRe3I3CUeOTsZTE/ExoOy', 1),
(3, 'SID10003', 'Amit Kumar', 'amit@example.com', '9988776655', '$2y$10$GLmeJYmmgFEz/xexN/5KYeOcifxK7WsPeRe3I3CUeOTsZTE/ExoOy', 0);

-- ---------------------------------------------------------------------
-- Table: tblissuedbookdetails
-- ---------------------------------------------------------------------
CREATE TABLE `tblissuedbookdetails` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `BookId` INT NOT NULL,
  `StudentId` VARCHAR(20) NOT NULL,
  `IssueDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DueDate` DATE NOT NULL,
  `ReturnDate` TIMESTAMP NULL DEFAULT NULL,
  `ReturnStatus` TINYINT(1) NOT NULL DEFAULT 0,
  `Fine` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `BookId` (`BookId`),
  KEY `StudentId` (`StudentId`),
  CONSTRAINT `fk_issue_book` FOREIGN KEY (`BookId`) REFERENCES `tblbooks` (`id`),
  CONSTRAINT `fk_issue_student` FOREIGN KEY (`StudentId`) REFERENCES `tblstudents` (`StudentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample issue records (dates are relative to the day you import the file)
INSERT INTO `tblissuedbookdetails` (`BookId`, `StudentId`, `IssueDate`, `DueDate`, `ReturnDate`, `ReturnStatus`, `Fine`) VALUES
(1, 'SID10001', DATE_SUB(NOW(), INTERVAL 5 DAY),  DATE_ADD(CURDATE(), INTERVAL 9 DAY),  NULL, 0, 0.00),
(9, 'SID10001', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY), 1, 10.00),
(3, 'SID10002', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY),  NULL, 0, 0.00),
(7, 'SID10002', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(NOW(), INTERVAL 27 DAY), 1, 0.00);

-- =====================================================================
--  BOOK SELLING MODULE
-- =====================================================================

-- ---------------------------------------------------------------------
-- Table: tblcart  (books a student has added to the cart)
-- ---------------------------------------------------------------------
CREATE TABLE `tblcart` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `StudentId` VARCHAR(20) NOT NULL,
  `BookId` INT NOT NULL,
  `Quantity` INT NOT NULL DEFAULT 1,
  `AddedDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_book` (`StudentId`, `BookId`),
  KEY `BookId` (`BookId`),
  CONSTRAINT `fk_cart_student` FOREIGN KEY (`StudentId`) REFERENCES `tblstudents` (`StudentId`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_book` FOREIGN KEY (`BookId`) REFERENCES `tblbooks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: tblorders  (one row per order placed by a student)
--   OrderStatus   : Pending, Confirmed, Dispatched, Delivered, Cancelled
--   PaymentStatus : Unpaid, Paid
-- ---------------------------------------------------------------------
CREATE TABLE `tblorders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `OrderNumber` VARCHAR(30) NOT NULL,
  `StudentId` VARCHAR(20) NOT NULL,
  `TotalAmount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `PaymentMethod` VARCHAR(50) NOT NULL,
  `PaymentStatus` VARCHAR(20) NOT NULL DEFAULT 'Unpaid',
  `OrderStatus` VARCHAR(20) NOT NULL DEFAULT 'Pending',
  `ShippingAddress` TEXT NULL,
  `ContactNumber` VARCHAR(15) NOT NULL,
  `AdminRemark` TEXT NULL,
  `OrderDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdationDate` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `OrderNumber` (`OrderNumber`),
  KEY `StudentId` (`StudentId`),
  CONSTRAINT `fk_order_student` FOREIGN KEY (`StudentId`) REFERENCES `tblstudents` (`StudentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Table: tblorderitems  (books inside an order)
--   BookName / ISBN / Price are copied at order time so the order
--   history stays correct even if the book is edited or deleted later.
-- ---------------------------------------------------------------------
CREATE TABLE `tblorderitems` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `OrderId` INT NOT NULL,
  `BookId` INT NULL,
  `BookName` VARCHAR(200) NOT NULL,
  `ISBNNumber` VARCHAR(30) NOT NULL,
  `Price` DECIMAL(10,2) NOT NULL,
  `Quantity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `OrderId` (`OrderId`),
  KEY `BookId` (`BookId`),
  CONSTRAINT `fk_item_order` FOREIGN KEY (`OrderId`) REFERENCES `tblorders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_book` FOREIGN KEY (`BookId`) REFERENCES `tblbooks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample orders
INSERT INTO `tblorders` (`id`, `OrderNumber`, `StudentId`, `TotalAmount`, `PaymentMethod`, `PaymentStatus`, `OrderStatus`, `ShippingAddress`, `ContactNumber`, `AdminRemark`, `OrderDate`) VALUES
(1, 'ORD100001', 'SID10001', 900.00,  'Cash on Delivery',       'Paid',   'Delivered', '12 MG Road, Pune, Maharashtra - 411001', '9876543210', 'Delivered to student. Cash received.', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 'ORD100002', 'SID10002', 1349.00, 'Pay at Library Counter', 'Unpaid', 'Pending',   NULL,                                     '9123456780', NULL,                                   DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `tblorderitems` (`OrderId`, `BookId`, `BookName`, `ISBNNumber`, `Price`, `Quantity`) VALUES
(1, 1,  'Clean Code',                            '9780132350884', 450.00, 2),
(2, 2,  'Introduction to Algorithms',            '9780262033848', 850.00, 1),
(2, 10, 'Sapiens: A Brief History of Humankind', '9780062316097', 499.00, 1);
