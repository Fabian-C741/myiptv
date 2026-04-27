<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;

echo "<h1>Updating App Configuration</h1>";

try {
    // 1. Create storage symlink
    echo "Creating storage symlink... ";
    Artisan::call('storage:link');
    echo "Done.<br>";

    // 2. Ensure directories exist
    $updatesPath = storage_path('app/public/updates');
    if (!file_exists($updatesPath)) {
        mkdir($updatesPath, 0755, true);
        echo "Created updates directory.<br>";
    }

    // 3. Update settings to point to the correct URL
    // We will support both /storage/updates/ and /apk/ by pointing to the one the user wants.
    // The current error is 404 on /storage/updates/Electrofabiptv.apk
    // Let's make sure the setting is correct.
    
    $currentUrl = Setting::get('app_apk_url');
    echo "Current APK URL: " . htmlspecialchars($currentUrl) . "<br>";

    // If the user is building with the script, it goes to /apk/Electrofabiptv.apk
    // But the controller uses /storage/updates/
    // Let's force it to /storage/updates/Electrofabiptv.apk for now as it's the "standard" Laravel way
    // and make sure we tell the user where to put the file.
    
    $targetUrl = url('/storage/updates/Electrofabiptv.apk');
    Setting::set('app_apk_url', $targetUrl);
    echo "Updated APK URL to: " . htmlspecialchars($targetUrl) . "<br>";

    echo "<br><b>Success!</b> Please make sure you have the file at: <code>storage/app/public/updates/Electrofabiptv.apk</code>";
    echo "<br>Or run the build script and I will update the script to put it there.";

} catch (\Exception $e) {
    echo "<br><b>Error:</b> " . $e->getMessage();
}
