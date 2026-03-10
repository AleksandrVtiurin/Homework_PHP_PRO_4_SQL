-- Создание таблицы магазина 
CREATE TABLE shop (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL
);

-- Создание таблицы продуктов
CREATE TABLE product (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    count INTEGER NOT NULL,
    shop_id INTEGER NOT NULL,
    FOREIGN KEY (shop_id) REFERENCES shop(id)
);

-- Создание таблицы покупателей
CREATE TABLE client (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL
);

-- Создание таблицы заказов
CREATE TABLE "order" (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at DATETIME NOT NULL,
    shop_id INTEGER NOT NULL,
    client_id INTEGER NOT NULL,
    FOREIGN KEY (shop_id) REFERENCES shop(id),
    FOREIGN KEY (client_id) REFERENCES client(id)
);

-- Создание таблицы соответствий продуктов и заказов
CREATE TABLE order_product (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES "order"(id),
    FOREIGN KEY (product_id) REFERENCES product(id)
);

-- Заполнение таблицы магазина
INSERT INTO shop (name, address) VALUES
    ('Магазин №1', 'ул. Ленина, 1'),
    ('Магазин №2', 'ул. Гагарина, 5'),
    ('Магазин №3', 'пр. Мира, 10'),
    ('Магазин №4', 'ул. Пушкина, 15'),
    ('Магазин №5', 'пр. Победы, 20');

-- Заполнение таблицы продуктов
INSERT INTO product (name, price, count, shop_id) VALUES
    ('Хлеб', 50.00, 100, 1),
    ('Молоко', 80.00, 50, 1),
    ('Сахар', 60.00, 30, 2),
    ('Соль', 20.00, 40, 3),
    ('Масло', 150.00, 20, 4);

-- Заполнение таблицы покупателей
INSERT INTO client (name, phone) VALUES
    ('Иванов Иван', '123-45-67'),
    ('Петров Петр', '234-56-78'),
    ('Сидоров Сидор', '345-67-89'),
    ('Смирнова Анна', '456-78-90'),
    ('Кузнецов Дмитрий', '567-89-01');

-- Заполнение таблицы заказов
INSERT INTO "order" (created_at, shop_id, client_id) VALUES
    ('2024-01-01 10:00:00', 1, 1),
    ('2024-01-02 11:00:00', 1, 2),
    ('2024-01-03 12:00:00', 2, 3),
    ('2024-01-04 13:00:00', 3, 4),
    ('2024-01-05 14:00:00', 4, 5);

-- Заполнение таблицы соответствий продуктов и заказов
INSERT INTO order_product (order_id, product_id, price) VALUES
    (1, 1, 50.00),
    (1, 2, 80.00),
    (2, 1, 50.00),
    (3, 3, 60.00),
    (4, 4, 20.00);
