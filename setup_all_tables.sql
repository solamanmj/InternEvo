-- Drop existing tables in correct order to avoid foreign key constraints
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS internships;
DROP TABLE IF EXISTS companies;
DROP TABLE IF EXISTS student_profiles;

-- Create student_profiles table
CREATE TABLE IF NOT EXISTS student_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    education TEXT,
    skills TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    description TEXT,
    website VARCHAR(255),
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create internships table
CREATE TABLE IF NOT EXISTS internships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    requirements TEXT,
    location VARCHAR(255),
    type VARCHAR(50),
    duration VARCHAR(50),
    stipend VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Active',
    deadline DATE,
    posted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    internship_id INT NOT NULL,
    resume_path VARCHAR(255),
    cover_letter TEXT,
    status VARCHAR(20) DEFAULT 'Pending',
    applied_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id),
    FOREIGN KEY (internship_id) REFERENCES internships(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test data
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
INSERT INTO companies (name, email, password, description, website, location)
VALUES (
    'Test Company',
    'company@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password is "password"
    'A test company for development',
    'https://example.com',
    'Mumbai, India'
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
    deadline
)
VALUES (
    1,
    'Web Development Intern',
    'Looking for a passionate web developer intern',
    'PHP, MySQL, HTML, CSS, JavaScript',
    'Mumbai, India',
    'Full-time',
    '3 months',
    '10000/month',
    DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
);

-- Insert test application
INSERT INTO applications (student_id, internship_id, status)
VALUES (1, 1, 'Pending');
