-- Database creation
DROP DATABASE IF EXISTS recipe_platform;
CREATE DATABASE recipe_platform;
USE recipe_platform;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('visitor', 'cook') NOT NULL DEFAULT 'visitor',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Recipes Table
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ingredients Table
CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed Data: Recipes
INSERT INTO recipes (user_id, title, description, category, rating) VALUES
(1, 'Beef Wellington', 'A classic English pie made of filet mignon coated with pâté and duxelles, wrapped in puff pastry.', 'Main Course', 4.8),
(1, 'Scrambled Eggs', 'Perfectly fluffy scrambled eggs.', 'Breakfast', 4.5),
(2, 'Roast Chicken', 'Simple and delicious roast chicken with herbs.', 'Main Course', 4.7),
(2, 'Pasta Carbonara', 'Authentic Italian pasta with eggs, cheese, and bacon.', 'Main Course', 4.6),
(1, 'Lobster Risotto', 'Creamy risotto with fresh lobster chunks.', 'Dinner', 4.9),
(2, 'Vegetable Stir Fry', 'Quick and healthy vegetable stir fry.', 'Vegetarian', 4.2),
(1, 'Chocolate Souffle', 'Rich and airy chocolate dessert.', 'Dessert', 4.8),
(2, 'Caesar Salad', 'Fresh romaine lettuce with parmesan and croutons.', 'Salad', 4.3),
(1, 'Fish and Chips', 'Crispy battered fish with golden fries.', 'Main Course', 4.4),
(2, 'Pancakes', 'Fluffy American style pancakes.', 'Breakfast', 4.5);

-- Seed Data: Ingredients
INSERT INTO ingredients (recipe_id, name, quantity) VALUES
(1, 'Beef Fillet', '1kg'), (1, 'Puff Pastry', '500g'),
(2, 'Eggs', '3'), (2, 'Butter', '10g'),
(3, 'Whole Chicken', '1.5kg'), (3, 'Rosemary', '2 sprigs'),
(4, 'Spaghetti', '400g'), (4, 'Pancetta', '150g'),
(5, 'Arborio Rice', '300g'), (5, 'Lobster Tail', '2'),
(6, 'Broccoli', '1 head'), (6, 'Soy Sauce', '2 tbsp'),
(7, 'Dark Chocolate', '200g'), (7, 'Sugar', '100g'),
(8, 'Lettuce', '1 head'), (8, 'Croutons', '1 cup'),
(9, 'Cod Fillet', '4'), (9, 'Potatoes', '500g'),
(10, 'Flour', '200g'), (10, 'Milk', '300ml');

-- Seed Data: Comments
INSERT INTO comments (recipe_id, user_id, comment) VALUES
(1, 3, 'Amazing recipe! Tasted like a restaurant meal.'),
(1, 4, 'A bit hard to make, but worth it.'),
(2, 3, 'Simple and tasty.'),
(3, 4, 'My family loved it.'),
(4, 3, 'Authentic taste, thanks!'),
(5, 4, 'So creamy and rich.'),
(6, 3, 'Good for a quick dinner.'),
(7, 4, 'Collapsed a bit but tasted great.'),
(8, 3, 'Fresh and crunchy.'),
(10, 4, 'Best breakfast ever.');
