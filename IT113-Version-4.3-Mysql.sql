-- User Authentication and Authorization
-- Manages user roles, registration, login, and access control

-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    CHECK (role_name IN ('Buyer', 'Seller', 'Admin'))
);

-- Create users table 
CREATE TABLE IF NOT EXISTS users (
    user_id CHAR(36) PRIMARY KEY DEFAULT (UUID()), -- UUID for user IDs
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    contacts VARCHAR(15) UNIQUE NOT NULL, -- New attribute for contact numbers
    email VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Store hashed passwords
    reg_date DATE DEFAULT CURRENT_DATE,
    date_of_birth DATE, -- Added date of birth field
    role_id INT,
    is_verified BOOLEAN DEFAULT FALSE, -- Email verification status
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL
);

-- Create user_tokens table
CREATE TABLE IF NOT EXISTS user_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36),
    token VARCHAR(255), -- JWT token for login sessions
    token_type VARCHAR(20) NOT NULL, -- 'JWT' for login tokens or 'EMAIL_VERIFICATION'
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When the token was issued
    expires_at TIMESTAMP, -- Expiration time for the token
    is_valid BOOLEAN DEFAULT TRUE, -- Indicates if the token is still valid
    email_verified BOOLEAN DEFAULT FALSE, -- Tracks email confirmation (used for 'EMAIL_VERIFICATION')
    CHECK (token_type IN ('JWT', 'EMAIL_VERIFICATION')),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Product Catalog and Attributes for Clothing Store
-- Manages product organization, categories, and attributes specific to clothing

-- Create product_category table
CREATE TABLE IF NOT EXISTS product_category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    image_url VARCHAR(255),
    size VARCHAR(10), -- Nullable: e.g., S, M, L, XL
    color VARCHAR(30), -- Nullable: e.g., Red, Blue
    material VARCHAR(50), -- Nullable: e.g., Cotton, Polyester
    date_added DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (category_id) REFERENCES product_category(category_id) ON DELETE SET NULL
);

-- Create product_inventory table
CREATE TABLE IF NOT EXISTS product_inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    quantity INT NOT NULL CHECK (quantity >= 0),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    cart_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36),
    product_id INT,
    quantity INT NOT NULL CHECK (quantity > 0),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id CHAR(36),
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    cart_id CHAR(36),
    total_amount DECIMAL(10, 2) NOT NULL, -- Calculated from the cart
    shipping_address VARCHAR(100) NOT NULL,
    order_status VARCHAR(20) NOT NULL DEFAULT 'Pending', -- Fixed order statuses
    CHECK (order_status IN ('Pending', 'Processing', 'Shipped', 'Completed')),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE
);

-- Create payment table
CREATE TABLE IF NOT EXISTS payment (
    payment_id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    order_id CHAR(36),
    payment_date DATE DEFAULT CURRENT_DATE,
    payment_method VARCHAR(20),
    payment_status VARCHAR(20) DEFAULT 'Pending',
    encrypted_payment_details BLOB, -- Store encrypted payment information
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- Insert dummy data into roles
INSERT INTO roles (role_name) VALUES 
('Buyer'),
('Seller'),
('Admin');

-- Insert dummy data into users
INSERT INTO users (first_name, last_name, contacts, email, password, date_of_birth, role_id, is_verified) VALUES
('John', 'Doe', '09123456789', 'john.doe@example.com', 'hashed_password_1', '1980-01-01', (SELECT role_id FROM roles WHERE role_name = 'Buyer'), TRUE),
('Jane', 'Smith', '09234567890', 'jane.smith@example.com', 'hashed_password_2', '1990-05-15', (SELECT role_id FROM roles WHERE role_name = 'Seller'), FALSE),
('Alice', 'Johnson', '09345678901', 'alice.johnson@example.com', 'hashed_password_3', '1985-07-22', (SELECT role_id FROM roles WHERE role_name = 'Admin'), TRUE),
('Bob', 'Brown', '09456789012', 'bob.brown@example.com', 'hashed_password_4', '2000-09-30', (SELECT role_id FROM roles WHERE role_name = 'Buyer'), TRUE),
('Carol', 'Davis', '09567890123', 'carol.davis@example.com', 'hashed_password_5', '1995-03-11', (SELECT role_id FROM roles WHERE role_name = 'Seller'), FALSE),
('Dave', 'Wilson', '09678901234', 'dave.wilson@example.com', 'hashed_password_6', '1992-06-18', (SELECT role_id FROM roles WHERE role_name = 'Admin'), TRUE),
('Eve', 'Taylor', '09789012345', 'eve.taylor@example.com', 'hashed_password_7', '1988-12-25', (SELECT role_id FROM roles WHERE role_name = 'Buyer'), TRUE),
('Frank', 'Anderson', '09890123456', 'frank.anderson@example.com', 'hashed_password_8', '1975-10-01', (SELECT role_id FROM roles WHERE role_name = 'Seller'), FALSE),
('Grace', 'Thomas', '09901234567', 'grace.thomas@example.com', 'hashed_password_9', '2001-02-14', (SELECT role_id FROM roles WHERE role_name = 'Admin'), TRUE),
('Hank', 'Moore', '09012345678', 'hank.moore@example.com', 'hashed_password_10', '1998-11-03', (SELECT role_id FROM roles WHERE role_name = 'Buyer'), TRUE);


-- Insert dummy data into product_category
INSERT INTO product_category (category_name) VALUES
('Shirts'),
('Pants'),
('Jackets'),
('Shoes'),
('Accessories');

-- Insert dummy data into products
INSERT INTO products (product_name, description, price, category_id, image_url, size, color, material) VALUES
('Casual Shirt', 'A comfortable casual shirt.', 29.99, (SELECT category_id FROM product_category WHERE category_name = 'Shirts'), 'url_to_image_1', 'M', 'Blue', 'Cotton'),
('Leather Jacket', 'Stylish leather jacket.', 99.99, (SELECT category_id FROM product_category WHERE category_name = 'Jackets'), 'url_to_image_2', 'L', 'Black', 'Leather'),
('Running Shoes', 'Lightweight running shoes.', 49.99, (SELECT category_id FROM product_category WHERE category_name = 'Shoes'), 'url_to_image_3', '10', 'Red', 'Synthetic'),
('Wool Scarf', 'Warm wool scarf.', 19.99, (SELECT category_id FROM product_category WHERE category_name = 'Accessories'), 'url_to_image_4', NULL, 'Gray', 'Wool'),
('Formal Pants', 'Elegant formal pants.', 39.99, (SELECT category_id FROM product_category WHERE category_name = 'Pants'), 'url_to_image_5', '32', 'Black', 'Polyester'),
('Winter Gloves', 'Insulated winter gloves.', 15.99, (SELECT category_id FROM product_category WHERE category_name = 'Accessories'), 'url_to_image_6', 'M', 'Brown', 'Wool'),
('Sunglasses', 'Stylish UV protection sunglasses.', 25.99, (SELECT category_id FROM product_category WHERE category_name = 'Accessories'), 'url_to_image_7', NULL, 'Black', 'Plastic'),
('Denim Jacket', 'Trendy denim jacket.', 59.99, (SELECT category_id FROM product_category WHERE category_name = 'Jackets'), 'url_to_image_8', 'M', 'Blue', 'Denim'),
('Belts', 'Fashionable belts.', 14.99, (SELECT category_id FROM product_category WHERE category_name = 'Accessories'), 'url_to_image_9', 'L', 'Black', 'Leather'),
('Sun Hat', 'Protective sun hat.', 12.99, (SELECT category_id FROM product_category WHERE category_name = 'Accessories'), 'url_to_image_10', 'One Size', 'Beige', 'Straw');

-- Insert dummy data into product_inventory
INSERT INTO product_inventory (product_id, quantity) VALUES
((SELECT product_id FROM products WHERE product_name = 'Casual Shirt'), 100),
((SELECT product_id FROM products WHERE product_name = 'Leather Jacket'), 50),
((SELECT product_id FROM products WHERE product_name = 'Running Shoes'), 75),
((SELECT product_id FROM products WHERE product_name = 'Wool Scarf'), 200),
((SELECT product_id FROM products WHERE product_name = 'Formal Pants'), 120),
((SELECT product_id FROM products WHERE product_name = 'Winter Gloves'), 150),
((SELECT product_id FROM products WHERE product_name = 'Sunglasses'), 80),
((SELECT product_id FROM products WHERE product_name = 'Denim Jacket'), 60),
((SELECT product_id FROM products WHERE product_name = 'Belts'), 90),
((SELECT product_id FROM products WHERE product_name = 'Sun Hat'), 110);

-- Insert dummy data into cart
INSERT INTO cart (user_id, product_id, quantity) VALUES
((SELECT user_id FROM users WHERE email = 'john.doe@example.com'), (SELECT product_id FROM products WHERE product_name = 'Casual Shirt'), 2),
((SELECT user_id FROM users WHERE email = 'jane.smith@example.com'), (SELECT product_id FROM products WHERE product_name = 'Leather Jacket'), 1),
((SELECT user_id FROM users WHERE email = 'alice.johnson@example.com'), (SELECT product_id FROM products WHERE product_name = 'Running Shoes'), 3),
((SELECT user_id FROM users WHERE email = 'bob.brown@example.com'), (SELECT product_id FROM products WHERE product_name = 'Wool Scarf'), 5),
((SELECT user_id FROM users WHERE email = 'carol.davis@example.com'), (SELECT product_id FROM products WHERE product_name = 'Formal Pants'), 2),
((SELECT user_id FROM users WHERE email = 'dave.wilson@example.com'), (SELECT product_id FROM products WHERE product_name = 'Winter Gloves'), 1),
((SELECT user_id FROM users WHERE email = 'eve.taylor@example.com'), (SELECT product_id FROM products WHERE product_name = 'Sunglasses'), 4),
((SELECT user_id FROM users WHERE email = 'frank.anderson@example.com'), (SELECT product_id FROM products WHERE product_name = 'Denim Jacket'), 2),
((SELECT user_id FROM users WHERE email = 'grace.thomas@example.com'), (SELECT product_id FROM products WHERE product_name = 'Belts'), 3),
((SELECT user_id FROM users WHERE email = 'hank.moore@example.com'), (SELECT product_id FROM products WHERE product_name = 'Sun Hat'), 1);

-- Insert dummy data into orders
INSERT INTO orders (user_id, cart_id, total_amount, shipping_address) VALUES
((SELECT user_id FROM users WHERE email = 'john.doe@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'john.doe@example.com')), 59.98, '123 Main St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'jane.smith@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'jane.smith@example.com')), 99.99, '456 Elm St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'alice.johnson@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'alice.johnson@example.com')), 149.97, '789 Oak St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'bob.brown@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'bob.brown@example.com')), 99.95, '101 Maple St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'carol.davis@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'carol.davis@example.com')), 39.99, '202 Birch St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'dave.wilson@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'dave.wilson@example.com')), 15.99, '303 Cedar St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'eve.taylor@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'eve.taylor@example.com')), 103.96, '404 Pine St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'frank.anderson@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'frank.anderson@example.com')), 59.99, '505 Spruce St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'grace.thomas@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'grace.thomas@example.com')), 44.97, '606 Fir St, Anytown, USA'),
((SELECT user_id FROM users WHERE email = 'hank.moore@example.com'), (SELECT cart_id FROM cart WHERE user_id = (SELECT user_id FROM users WHERE email = 'hank.moore@example.com')), 12.99, '707 Walnut St, Anytown, USA');

-- Insert dummy data into payment
INSERT INTO payment (order_id, payment_method, encrypted_payment_details) VALUES
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'john.doe@example.com')), 'Credit Card', 'encrypted_details_1'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'jane.smith@example.com')), 'PayPal', 'encrypted_details_2'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'alice.johnson@example.com')), 'Credit Card', 'encrypted_details_3'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'bob.brown@example.com')), 'Credit Card', 'encrypted_details_4'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'carol.davis@example.com')), 'PayPal', 'encrypted_details_5'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'dave.wilson@example.com')), 'Credit Card', 'encrypted_details_6'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'eve.taylor@example.com')), 'Credit Card', 'encrypted_details_7'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'frank.anderson@example.com')), 'PayPal', 'encrypted_details_8'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'grace.thomas@example.com')), 'Credit Card', 'encrypted_details_9'),
((SELECT order_id FROM orders WHERE user_id = (SELECT user_id FROM users WHERE email = 'hank.moore@example.com')), 'PayPal', 'encrypted_details_10');
