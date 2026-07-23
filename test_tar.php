<?php
$uploadDir = 'C:/Users/Tristan/OneDrive/Desktop/Files/HTML CSS PRACTICE/WEBPAGE PROJECTS/LibroSys/uploads/manga/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Create a simple test ZIP using Windows tar (we already know tar works)
$testImg = $uploadDir . 'test_page.jpg';
$imgData = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAFsAD/2Q==');
file_put_contents($testImg, $imgData);

$zipPath = $uploadDir . 'test_upload.zip';
$tarCmd = 'tar -a -c -f ' . escapeshellarg($zipPath) . ' -C ' . escapeshellarg($uploadDir) . ' test_page.jpg';
exec($tarCmd . ' 2>&1', $output, $returnVar);
echo "tar create: return=$returnVar output=" . implode(';', $output) . "\n";

if (file_exists($zipPath)) {
    echo "ZIP created: " . filesize($zipPath) . " bytes\n";
    
    // Test extraction
    $extractDir = $uploadDir . 'test_extract_' . time() . '/';
    mkdir($extractDir, 0777, true);
    
    $tarCmd2 = 'tar -xf ' . escapeshellarg($zipPath) . ' -C ' . escapeshellarg($extractDir);
    exec($tarCmd2 . ' 2>&1', $output2, $returnVar2);
    echo "tar extract: return=$returnVar2 output=" . implode(';', $output2) . "\n";
    
    if (file_exists($extractDir . 'test_page.jpg')) {
        echo "Extracted file size: " . filesize($extractDir . 'test_page.jpg') . "\n";
    } else {
        echo "Extracted file NOT found\n";
        $files = glob($extractDir . '*');
        echo "Files in extract dir: " . count($files) . "\n";
        foreach ($files as $f) echo "  " . basename($f) . "\n";
    }
    
    // Cleanup
    @unlink($extractDir . 'test_page.jpg');
    @rmdir($extractDir);
}

@unlink($testImg);
@unlink($zipPath);
