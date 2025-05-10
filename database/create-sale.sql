CREATE TABLE sale (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    date_sold DATE NOT NULL,
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);