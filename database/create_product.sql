CREATE TABLE Product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(30) NOT NULL,
    description VARCHAR(100) NOT NULL,
    category VARCHAR(20) NOT NULL,
    price float NOT NULL,
    seller_id INT NOT NULL,
    status varchar(15) NOT NULL,
    FOREIGN KEY (seller_id) REFERENCES User(user_id)
)