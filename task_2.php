<?php
class Database {
    private static $instance = null;
    private $connection;
    private function __construct() {
        $this->connection = new PDO('sqlite:shop.db');
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
interface DatabaseWrapper
{
    public function insert(array $tableColumns, array $values): array;
    public function update(int $id, array $values): array;
    public function find(int $id): array;
    public function delete(int $id): bool;
}
abstract class BaseDatabaseWrapper implements DatabaseWrapper
{
    protected $pdo;
    protected $tableName;
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    abstract protected function getTableName(): string;
    public function insert(array $tableColumns, array $values): array {
        try {
            $columns = implode(', ', $tableColumns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $sql = "INSERT INTO {$this->getTableName()} ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
            $id = $this->pdo->lastInsertId();
            return $this->find($id);
        } catch (PDOException $e) {
            throw new Exception("Ошибка при вставке: " . $e->getMessage());
        }
    }
    
    public function update(int $id, array $values): array {
        try {
            $setParts = [];
            foreach (array_keys($values) as $column) {
                $setParts[] = "$column = ?";
            }
            $setString = implode(', ', $setParts);
            $sql = "UPDATE {$this->getTableName()} SET $setString WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $executeValues = array_values($values);
            $executeValues[] = $id;
            $stmt->execute($executeValues);
            return $this->find($id);
        } catch (PDOException $e) {
            throw new Exception("Ошибка при обновлении: " . $e->getMessage());
        }
    }
    public function find(int $id): array {
        try {
            $sql = "SELECT * FROM {$this->getTableName()} WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                throw new Exception("Запись с id $id не найдена в таблице {$this->getTableName()}");
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Ошибка при поиске: " . $e->getMessage());
        }
    }
    
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM {$this->getTableName()} WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Ошибка при удалении: " . $e->getMessage());
        }
    }
}

class ShopWrapper extends BaseDatabaseWrapper {
    protected function getTableName(): string {
        return 'shop';
    }
}

class ProductWrapper extends BaseDatabaseWrapper {
    protected function getTableName(): string {
        return 'product';
    }
}

class ClientWrapper extends BaseDatabaseWrapper {
    protected function getTableName(): string {
        return 'client';
    }
}

class OrderWrapper extends BaseDatabaseWrapper {
    protected function getTableName(): string {
        return '"order"'; 
    }
}

class OrderProductWrapper extends BaseDatabaseWrapper {
    protected function getTableName(): string {
        return 'order_product';
    }
}

function printResult($title, $data) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo $title . "\n";
    echo str_repeat("=", 50) . "\n";
    if (is_array($data)) {
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $index => $item) {
                echo "Запись " . ($index + 1) . ":\n";
                foreach ($item as $key => $value) {
                    echo "  $key: $value\n";
                }
                echo "\n";
            }
        } else {
            foreach ($data as $key => $value) {
                echo "$key: $value\n";
            }
        }
    } else {
        echo $data ? "true" : "false";
    }
    echo "\n";
}

try {
    echo "ТЕСТИРОВАНИЕ РАБОТЫ КЛАССОВ\n";

    echo "\n--- ТЕСТИРОВАНИЕ ShopWrapper ---\n";
    $shopWrapper = new ShopWrapper();

    printResult("Поиск магазина с id=1:", $shopWrapper->find(1));
 
    printResult("Вставка нового магазина:", 
        $shopWrapper->insert(
            ['name', 'address'],
            ['Магазин №6', 'ул. Новая, 100']
        )
    );

    printResult("Обновление магазина с id=6:", 
        $shopWrapper->update(6, ['name' => 'Супермаркет №6', 'address' => 'пр. Обновленный, 200'])
    );

    echo "\n--- ТЕСТИРОВАНИЕ ClientWrapper ---\n";
    $clientWrapper = new ClientWrapper();

    printResult("Вставка нового клиента:", 
        $clientWrapper->insert(
            ['name', 'phone'],
            ['Новиков Алексей', '999-99-99']
        )
    );

    printResult("Обновление клиента с id=6:", 
        $clientWrapper->update(6, ['name' => 'Новиков А.С.', 'phone' => '888-88-88'])
    );

    echo "\n--- ТЕСТИРОВАНИЕ ProductWrapper ---\n";
    $productWrapper = new ProductWrapper();

    printResult("Вставка нового продукта:", 
        $productWrapper->insert(
            ['name', 'price', 'count', 'shop_id'],
            ['Чай', 120.00, 50, 1]
        )
    );

    printResult("Поиск продукта с id=6:", $productWrapper->find(6));

    echo "\n--- ТЕСТИРОВАНИЕ OrderWrapper ---\n";
    $orderWrapper = new OrderWrapper();

    printResult("Вставка нового заказа:", 
        $orderWrapper->insert(
            ['created_at', 'shop_id', 'client_id'],
            [date('Y-m-d H:i:s'), 1, 6]
        )
    );

    echo "\n--- ТЕСТИРОВАНИЕ OrderProductWrapper ---\n";
    $orderProductWrapper = new OrderProductWrapper();
  
    printResult("Вставка позиции заказа:", 
        $orderProductWrapper->insert(
            ['order_id', 'product_id', 'price'],
            [6, 6, 120.00]
        )
    );
    
    echo "\n--- ТЕСТИРОВАНИЕ УДАЛЕНИЯ ---\n";
    
    printResult("Удаление позиции заказа с id=6:", $orderProductWrapper->delete(6));
    
    printResult("Удаление заказа с id=6:", $orderWrapper->delete(6));
    
    printResult("Удаление продукта с id=6:", $productWrapper->delete(6));
    
    printResult("Удаление клиента с id=6:", $clientWrapper->delete(6));
    
    printResult("Удаление магазина с id=6:", $shopWrapper->delete(6));
    
    echo "\n--- ПРОВЕРКА УДАЛЕНИЯ ---\n";
    
    try {
        $shopWrapper->find(6);
    } catch (Exception $e) {
        echo "Магазин с id=6 не найден: " . $e->getMessage() . "\n";
    }
    
    try {
        $clientWrapper->find(6);
    } catch (Exception $e) {
        echo "Клиент с id=6 не найден: " . $e->getMessage() . "\n";
    }
    
    try {
        $productWrapper->find(6);
    } catch (Exception $e) {
        echo "Продукт с id=6 не найден: " . $e->getMessage() . "\n";
    }
    
    try {
        $orderWrapper->find(6);
    } catch (Exception $e) {
        echo "Заказ с id=6 не найден: " . $e->getMessage() . "\n";
    }
    
    try {
        $orderProductWrapper->find(6);
    } catch (Exception $e) {
        echo "Позиция заказа с id=6 не найдена: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
