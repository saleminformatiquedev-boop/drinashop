<?php
$csvFile = __DIR__ . '/../src/wc-product-export-23-7-2026-1784835240835.csv';
$imagesDir = __DIR__ . '/../public/imagesProduits/';

if (!file_exists($imagesDir)) {
    mkdir($imagesDir, 0777, true);
}

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Read headers
    $headers = fgetcsv($handle, 10000, ",");
    $imageColIndex = array_search('Images', $headers);
    
    if ($imageColIndex === false) {
        die("Colonne 'Images' introuvable dans le CSV.\n");
    }

    echo "Début du téléchargement des images...\n";
    $count = 0;
    
    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        if (!isset($data[$imageColIndex]) || empty($data[$imageColIndex])) continue;
        
        $imageUrls = explode(',', $data[$imageColIndex]);
        
        foreach ($imageUrls as $url) {
            $url = trim($url);
            if (empty($url)) continue;
            
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if (empty($filename)) continue;
            
            $localPath = $imagesDir . $filename;
            
            if (!file_exists($localPath)) {
                echo "Téléchargement de: $filename...\n";
                // Suppression des warnings en cas de 404
                $imageContent = @file_get_contents($url);
                if ($imageContent !== false) {
                    file_put_contents($localPath, $imageContent);
                    $count++;
                } else {
                    echo "Échec du téléchargement pour: $url\n";
                }
            } else {
                echo "Déjà présent: $filename\n";
            }
        }
    }
    fclose($handle);
    echo "Terminé. $count images téléchargées.\n";
} else {
    echo "Impossible d'ouvrir le fichier CSV.\n";
}
