CREATE TABLE IF NOT EXISTS user (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL,
  surname VARCHAR(30) NOT NULL,
  email VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL
  );

  CREATE TABLE IF NOT EXISTS product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(30) NOT NULL,
    description VARCHAR(100) NOT NULL,
    category VARCHAR(20) NOT NULL,
    price float NOT NULL,
    seller_id INT NOT NULL,
    status varchar(15) NOT NULL,
    FOREIGN KEY (seller_id) REFERENCES user(user_id)
);

CREATE TABLE IF NOT EXISTS `order` (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  order_date DATE NOT NULL,
  price float NOT NULL,
  status VARCHAR(15) NOT NULL,
  customer_id INT NOT NULL,
  product_id INT NOT NULL,
  FOREIGN KEY (customer_id) REFERENCES user(user_id),
  FOREIGN KEY (product_id) REFERENCES product(product_id)
);

CREATE TABLE IF NOT EXISTS address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_line VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    province VARCHAR(50),
    postal_code VARCHAR(10),
    country VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
);

CREATE TABLE IF NOT EXISTS shipment(
    shipment_id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_method VARCHAR(15) NOT NULL,
    delivery_status VARCHAR(15) NOT NULL,
    order_id INT NOT NULL,
    address_id INT NULL,
    shipment_date DATE NULL,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id),
    FOREIGN KEY (address_id) REFERENCES address(address_id)
);

CREATE TABLE IF NOT EXISTS payment(
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    amount float NOT NULL,
    payment_date DATE NOT NULL,
    order_id INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES `order`(order_id)
);

CREATE TABLE IF NOT EXISTS sale (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    date_sold DATE NOT NULL,
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);