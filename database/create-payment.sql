CREATE TABLE Payment(
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    amount float NOT NULL,
    payment_date DATE NOT NULL,
    order_id INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES `Order`(order_id)
)