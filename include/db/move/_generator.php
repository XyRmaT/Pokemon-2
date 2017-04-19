<?php


$conn = new mysqli('localhost', 'root', '', 'pokemon');

$query = $conn->query('SELECT move_id, name_en FROM pkm_movedata');

$files = glob('./*');
foreach ($files as $file) {
    if (preg_match('/(_generator|_base)/', $file)) continue;
    unlink($file);
}

echo '<pre>';
$pokemon = [];
while ($info = $query->fetch_assoc()) {
    echo implode(', ', $info) . PHP_EOL;
    $fp = fopen(($name = trim(preg_replace('/[^A-Za-z]/', '', $info['name_en']))) . '.php', 'w+');
    fwrite($fp, <<<EOF
<?php

class Move$name extends MoveBase {

}
EOF
    );
    fclose($fp);
}

