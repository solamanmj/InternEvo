-- Connect to internevo database
USE internevo;

-- Create student_profiles table
CREATE TABLE IF NOT EXISTS student_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    contact_number VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create degrees table
CREATE TABLE IF NOT EXISTS degrees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    degree_name VARCHAR(100) NOT NULL,
    degree_type ENUM('Diploma', 'Bachelor', 'Master', 'Doctorate', 'Certificate') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create student_education table
CREATE TABLE IF NOT EXISTS student_education (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    degree_id INT NOT NULL,
    field_id INT,
    institution_name VARCHAR(200) NOT NULL,
    specialization VARCHAR(100),
    start_date DATE,
    end_date DATE,
    percentage DECIMAL(5,2),
    cgpa DECIMAL(4,2),
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (degree_id) REFERENCES degrees(id),
    FOREIGN KEY (field_id) REFERENCES fields_of_study(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert common degrees
INSERT INTO degrees (degree_name, degree_type) VALUES 
('BTech', 'Bachelor'),
('BE', 'Bachelor'),
('BSc', 'Bachelor'),
('BCA', 'Bachelor'),
('MCA', 'Master'),
('MBA', 'Master'),
('MTech', 'Master'),
('MSc', 'Master'),
('PhD', 'Doctorate'),
('Diploma in Computer Science', 'Diploma'),
('Diploma in Engineering', 'Diploma'),
('BBA', 'Bachelor'),
('BCom', 'Bachelor'),
('MA', 'Master'),
('MCom', 'Master'),
('BArch', 'Bachelor'),
('MArch', 'Master'),
('BPharm', 'Bachelor'),
('MPharm', 'Master'),
('MBBS', 'Bachelor'),
('MD', 'Master'),
('BDS', 'Bachelor'),
('MDS', 'Master');

-- Add indexes for better performance
ALTER TABLE student_profiles ADD INDEX idx_user_id (user_id);
ALTER TABLE student_education ADD INDEX idx_student_id (student_id);
ALTER TABLE student_education ADD INDEX idx_degree_id (degree_id);

-- Create fields_of_study table
CREATE TABLE IF NOT EXISTS fields_of_study (
    id INT PRIMARY KEY AUTO_INCREMENT,
    field_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create degree_fields mapping table
CREATE TABLE IF NOT EXISTS degree_fields (
    id INT PRIMARY KEY AUTO_INCREMENT,
    degree_id INT NOT NULL,
    field_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (degree_id) REFERENCES degrees(id),
    FOREIGN KEY (field_id) REFERENCES fields_of_study(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert fields of study data
INSERT INTO fields_of_study (field_name, description) VALUES 
-- Engineering Fields
('Computer Science Engineering', 'Study of computer systems, programming, and technology'),
('Electrical Engineering', 'Study of electrical systems and electronics'),
('Mechanical Engineering', 'Study of mechanical systems and manufacturing'),
('Civil Engineering', 'Study of construction and infrastructure'),
('Electronics & Communication', 'Study of electronic systems and communication'),
('Information Technology', 'Study of information systems and technology'),
('Chemical Engineering', 'Study of chemical processes and materials'),
('Aerospace Engineering', 'Study of aircraft and spacecraft systems'),

-- Science Fields
('Physics', 'Study of matter, energy, and their interactions'),
('Chemistry', 'Study of substances and their interactions'),
('Mathematics', 'Study of numbers, quantities, and shapes'),
('Biology', 'Study of living organisms'),

-- Commerce Fields
('Accounting', 'Study of financial records and reporting'),
('Finance', 'Study of money management and investments'),
('Marketing', 'Study of promoting and selling products/services'),
('Business Administration', 'Study of business management principles'),

-- Arts Fields
('Economics', 'Study of production, distribution, and consumption'),
('Psychology', 'Study of human behavior and mental processes'),
('English Literature', 'Study of English language texts and writing'),
('History', 'Study of past events and their significance'),

-- Medical Fields
('Medicine', 'Study of health and disease treatment'),
('Dentistry', 'Study of oral health and treatment'),
('Pharmacy', 'Study of drugs and medication'),
('Nursing', 'Study of patient care and health management'),

-- Computer Applications
('Software Development', 'Study of software creation and maintenance'),
('Database Management', 'Study of data organization and management'),
('Network Administration', 'Study of computer network systems'),
('Artificial Intelligence', 'Study of intelligent computer systems'),

-- Management Fields
('Human Resource Management', 'Study of personnel management'),
('Operations Management', 'Study of business operations'),
('Project Management', 'Study of project planning and execution'),
('Supply Chain Management', 'Study of supply chain systems');

-- Map fields to degrees (example mappings)
INSERT INTO degree_fields (degree_id, field_id) 
SELECT d.id, f.id 
FROM degrees d, fields_of_study f 
WHERE d.degree_name = 'BTech' 
AND f.field_name IN (
    'Computer Science Engineering',
    'Electrical Engineering',
    'Mechanical Engineering',
    'Civil Engineering',
    'Electronics & Communication',
    'Information Technology',
    'Chemical Engineering',
    'Aerospace Engineering'
);

-- Map MCA fields
INSERT INTO degree_fields (degree_id, field_id) 
SELECT d.id, f.id 
FROM degrees d, fields_of_study f 
WHERE d.degree_name = 'MCA' 
AND f.field_name IN (
    'Software Development',
    'Database Management',
    'Network Administration',
    'Artificial Intelligence'
);

-- Map MBA fields
INSERT INTO degree_fields (degree_id, field_id) 
SELECT d.id, f.id 
FROM degrees d, fields_of_study f 
WHERE d.degree_name = 'MBA' 
AND f.field_name IN (
    'Human Resource Management',
    'Operations Management',
    'Project Management',
    'Supply Chain Management',
    'Marketing',
    'Finance'
);

-- Add indexes for better performance
ALTER TABLE degree_fields ADD INDEX idx_degree_id (degree_id);
ALTER TABLE degree_fields ADD INDEX idx_field_id (field_id); 