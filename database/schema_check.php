<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = Schema::getColumnListing('part_ng');
echo "part_ng table columns:\n";
foreach ($columns as $col) {
    $type = Schema::getColumnType('part_ng', $col);
    echo "  $col ($type)\n";
}
