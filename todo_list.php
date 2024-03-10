<?php
require '/etc/mysql/mysql.conf.d/vendor/autoload.php';

$user = "user";
$password = "123456789";
$database = "example_database";
$table = "todo_list";

try {
    // Create a Redis client
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host' => 'localhost', // Replace with your Redis server host
        'port' => 6379,
    ]);

    // Check if data is in Redis cache
    $cachedData = $redis->get('todo_list_cache');

    if ($cachedData !== null) {
        // If data is in cache, use cached data
        $data = json_decode($cachedData, true);
    } else {
        // If data is not in cache, fetch from MySQL
        $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
        $data = [];
        foreach ($db->query("SELECT content FROM $table") as $row) {
            $data[] = $row['content'];
        }

        // Store data in Redis cache for future requests
        $redis->set('todo_list_cache', json_encode($data));
        // Set cache expiration time if needed: $redis->expire('todo_list_cache', 3600);
    }

    // Display data
    echo "<h2>TODO</h2><ol>";
    foreach ($data as $content) {
        echo "<li>$content</li>";
    }
    echo "</ol>";
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

