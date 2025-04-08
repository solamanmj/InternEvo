-- Drop existing tables in correct order to avoid foreign key constraints
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS internships;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS student_profiles;

-- Create student_profiles table
CREATE TABLE student_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    education TEXT,
    skills TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create companies table
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location VARCHAR(100),
    website VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create internships table
CREATE TABLE internships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    requirements TEXT,
    location VARCHAR(100),
    type ENUM('Full-time', 'Part-time', 'Remote') NOT NULL,
    duration VARCHAR(50),
    stipend DECIMAL(10,2),
    status ENUM('Active', 'Closed') DEFAULT 'Active',
    posted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deadline DATE,
    FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create applications table
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    internship_id INT NOT NULL,
    status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resume_path VARCHAR(255),
    cover_letter TEXT,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id),
    FOREIGN KEY (internship_id) REFERENCES internships(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test data
-- Insert test student
INSERT INTO student_profiles (first_name, last_name, email, password, phone, education, skills)
VALUES (
    'Test',
    'Student',
    'test@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password is "password"
    '1234567890',
    'Bachelor of Technology',
    'PHP, MySQL, Web Development'
);

-- Insert test company
INSERT INTO companies (name, description, location, website)
VALUES (
    'Tech Corp',
    'A leading technology company',
    'Mumbai, India',
    'https://techcorp.example.com'
);

-- Insert test internship
INSERT INTO internships (
    company_id,
    title,
    description,
    requirements,
    location,
    type,
    duration,
    stipend,
    status,
    deadline
)
VALUES (
    1,
    'Web Developer Intern',
    'Join our team as a web developer intern and work on exciting projects',
    'Knowledge of PHP, MySQL, HTML, CSS, JavaScript',
    'Mumbai, India',
    'Full-time',
    '3 months',
    15000.00,
    'Active',
    DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
);
