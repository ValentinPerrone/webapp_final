<?php

$host = "localhost";
$db   = "recipe_platform";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $users = [
        ['ChefGordon','gordon@example.com','password123','cook'],
        ['JamieOliver','jamie@example.com','password123','cook'],
        ['HungryJoe','joe@example.com','password123','visitor'],
        ['AliceEats','alice@example.com','password123','visitor'],
        ['SpicyMaria','maria@example.com','password123','cook'],
        ['BBQKing','bbqking@example.com','password123','cook'],
        ['SweetToothSam','sam@example.com','password123','visitor'],
        ['VeganVera','vera@example.com','password123','visitor'],
        ['MidnightSnacker','snacker@example.com','password123','visitor'],
        ['PastaPro','pasta@example.com','password123','cook']
    ];

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");

    foreach ($users as $u) {
        $hashedPassword = password_hash($u[2], PASSWORD_BCRYPT);
        $stmt->execute([$u[0], $u[1], $hashedPassword, $u[3]]);
    }

    echo "Users inserted successfully!";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

?>