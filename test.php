<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = Livewire\Livewire::test(App\Livewire\Downloader::class);
    $component->set("url", "https://www.youtube.com/watch?v=dQw4w9WgXcQ");
    $component->call("submit");
    $downloadId = $component->get("downloadId");
    
    $vd = App\Models\VideoDownload::find($downloadId);
    $vd->update(["status" => "completed", "file_path" => "downloads/test.mp4"]);
    
    $component->call("checkStatus");
    echo "SUCCESS: " . $component->get("status");
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

