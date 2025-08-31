<?php

$host = '127.0.0.1';
$db   = 'city';
$user = 'heidi';
$pass = 'xxxxxxxx';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$where = $_REQUEST['where'] ?? false;

if ($where) {

    $stmt = $pdo->query("
    SELECT * FROM locations l 
    WHERE l.id = $where
    ");


} else {

// Get the player's location

    $stmt = $pdo->query('
    SELECT * FROM locations l 
    JOIN player p on l.frame = p.frame and l.scene = p.scene
    WHERE p.id = 1
    ');

}
$loc = $stmt->fetch(PDO::FETCH_OBJ);

// Get the actions available for this player, frame and scene


// Get the adjacent locations, (Todo optimize to not include longtexts)

$sql = '
    SELECT * FROM locations l
    
    WHERE
        l.frame=? AND
        l.scene=? AND
        (l.south = ? OR
        l.north = ? OR
        l.west = ? OR
        l.east = ?)
';

$stmt = $pdo->prepare($sql);
$stmt->execute([$loc->frame, $loc->scene, $loc->north, $loc->south, $loc->east, $loc->west]);

while ($row = $stmt->fetch(PDO::FETCH_OBJ))
{
    if ($row->south == $loc->id) $loc->north = $row;
    if ($row->north == $loc->id) $loc->south = $row;
    if ($row->east == $loc->id) $loc->west = $row;
    if ($row->west == $loc->id) $loc->east = $row;
}
exit(json_encode($loc));

