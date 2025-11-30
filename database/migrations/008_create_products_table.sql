-- Create products table for e-commerce functionality
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    category ENUM('artisan_crafts', 'textiles', 'art', 'fresh_produce', 'cultural_items', 'traditional_clothing', 'accessories', 'local_brands', 'electronics', 'beauty_products', 'food_items', 'other') NOT NULL,
    subcategory VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2) DEFAULT NULL, -- For showing discounts
    cost_price DECIMAL(10, 2) DEFAULT NULL, -- Vendor's cost (private)
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    sku VARCHAR(100) UNIQUE,
    barcode VARCHAR(100),
    weight DECIMAL(8, 2) DEFAULT NULL, -- In kg
    dimensions VARCHAR(100), -- e.g., "20x15x5 cm"
    status ENUM('active', 'inactive', 'out_of_stock', 'discontinued') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    digital BOOLEAN DEFAULT FALSE, -- For digital products
    requires_shipping BOOLEAN DEFAULT TRUE,
    tax_rate DECIMAL(5, 2) DEFAULT 0.00,
    meta_title VARCHAR(255),
    meta_description TEXT,
    tags TEXT, -- JSON array of tags
    images TEXT, -- JSON array of image URLs
    cultural_context TEXT, -- Explanation for newcomers
    origin_story TEXT, -- Artisan/product background
    care_instructions TEXT,
    materials TEXT, -- What it's made from
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price),
    FULLTEXT idx_search (name, description, tags)
);

-- Create product variants table (for size, color, etc.)
CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(100) NOT NULL, -- e.g., "Large Red", "Size M"
    sku VARCHAR(100) UNIQUE,
    price DECIMAL(10, 2), -- Override product price if different
    stock_quantity INT DEFAULT 0,
    attributes TEXT, -- JSON: {"size": "L", "color": "red"}
    image_url VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_sku (sku)
);

-- Create shopping cart table
CREATE TABLE IF NOT EXISTS shopping_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255), -- For guest users
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    UNIQUE KEY unique_cart_item (user_id, session_id, product_id, variant_id)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    guest_email VARCHAR(255),
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
    payment_method ENUM('paystack', 'mobile_money', 'cash_on_delivery', 'bank_transfer') DEFAULT 'paystack',
    payment_reference VARCHAR(255),
    
    -- Pricing
    subtotal DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) DEFAULT 0.00,
    shipping_amount DECIMAL(10, 2) DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    
    -- Shipping info
    shipping_name VARCHAR(255),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_region VARCHAR(100),
    shipping_method ENUM('pickup', 'delivery', 'courier') DEFAULT 'delivery',
    
    -- Billing info (can be same as shipping)
    billing_name VARCHAR(255),
    billing_phone VARCHAR(20),
    billing_address TEXT,
    billing_city VARCHAR(100),
    billing_region VARCHAR(100),
    
    notes TEXT,
    tracking_number VARCHAR(255),
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_order_number (order_number),
    INDEX idx_created_at (created_at)
);

-- Create order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    vendor_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL, -- Snapshot at time of order
    variant_name VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    product_data TEXT, -- JSON snapshot of product details
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE RESTRICT,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id),
    INDEX idx_vendor_id (vendor_id)
);

-- Create product reviews table (separate from service reviews)
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT, -- Link to purchase for verified reviews
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    comment TEXT,
    images TEXT, -- JSON array of review image URLs
    verified_purchase BOOLEAN DEFAULT FALSE,
    helpful_votes INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rating (rating),
    INDEX idx_verified (verified_purchase),
    UNIQUE KEY unique_user_product_review (user_id, product_id, order_id)
);

-- Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Create product categories table for better organization
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT DEFAULT NULL,
    image_url VARCHAR(500),
    cultural_significance TEXT, -- Why this category matters in Ghana
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    INDEX idx_parent_id (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order)
);

-- Insert default product categories
INSERT INTO product_categories (name, slug, description, cultural_significance, sort_order) VALUES
('Artisan Crafts', 'artisan-crafts', 'Handmade crafts by local artisans', 'Ghana has a rich tradition of craftsmanship, from wood carvings to pottery. These items represent centuries of cultural heritage.', 1),
('Textiles & Fabrics', 'textiles', 'Traditional and modern fabrics', 'Kente cloth and other traditional textiles are symbols of Ghanaian identity and are worn during important ceremonies.', 2),
('Art & Sculptures', 'art', 'Local artwork and sculptures', 'Ghanaian art tells stories of history, spirituality, and daily life. Each piece carries cultural meaning.', 3),
('Fresh Produce', 'fresh-produce', 'Fresh fruits, vegetables, and local ingredients', 'Local produce is fresher, supports farmers, and includes unique Ghanaian ingredients you won\'t find elsewhere.', 4),
('Cultural Items', 'cultural-items', 'Traditional ceremonial and cultural objects', 'These items are used in traditional ceremonies and cultural practices, each with specific meanings and uses.', 5),
('Traditional Clothing', 'traditional-clothing', 'Authentic Ghanaian clothing and accessories', 'Traditional clothing like dashiki and kaba are not just fashion - they represent cultural pride and identity.', 6),
('Accessories', 'accessories', 'Jewelry, bags, and traditional accessories', 'Ghanaian accessories often incorporate traditional symbols and materials, each with cultural significance.', 7),
('Local Brands', 'local-brands', 'Products from Ghanaian businesses', 'Supporting local brands helps the Ghanaian economy and gives you authentic, locally-made products.', 8),
('Beauty & Personal Care', 'beauty', 'Local beauty products and personal care items', 'Many Ghanaian beauty products use traditional ingredients like shea butter and black soap.', 9),
('Food & Spices', 'food-spices', 'Local spices, snacks, and packaged foods', 'Authentic Ghanaian spices and foods to help you cook traditional dishes at home.', 10);
