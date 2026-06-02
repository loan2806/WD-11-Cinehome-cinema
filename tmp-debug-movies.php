<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Movie::with('showtimes')->get() as $movie) {
    echo 'Movie: ' . $movie->ten_phim . PHP_EOL;
    echo 'Release: ' . $movie->release_date . PHP_EOL;
    foreach ($movie->showtimes as $showtime) {
        echo '  ' . $showtime->show_date . ' ' . $showtime->show_time . PHP_EOL;
    }
    echo str_repeat('-', 30) . PHP_EOL;
}
