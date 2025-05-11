-- Create professors table
CREATE TABLE IF NOT EXISTS `professors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample professor data (password is 'professor123' hashed)
INSERT INTO `professors` (`name`, `email`, `password`, `department`, `phone`) VALUES
('Dr. John Smith', 'john.smith@dslab.edu', '$2y$12$9bJU0cczBSVg/ZF.amTPD.wiFidlxBJP5uaQ7T/bQOvh824VH7Fz.', 'Computer Science', '123-456-7890'),
('Dr. Emily Johnson', 'emily.johnson@dslab.edu', '$2y$12$9bJU0cczBSVg/ZF.amTPD.wiFidlxBJP5uaQ7T/bQOvh824VH7Fz.', 'Electrical Engineering', '123-456-7891'),
('Dr. Michael Brown', 'michael.brown@dslab.edu', '$2y$12$9bJU0cczBSVg/ZF.amTPD.wiFidlxBJP5uaQ7T/bQOvh824VH7Fz.', 'Mechanical Engineering', '123-456-7892');

-- Note: The default password for all professors is 'professor123'
-- The passwords are properly hashed using PHP's password_hash() function with PASSWORD_DEFAULT
