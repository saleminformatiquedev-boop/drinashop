<?php
function get_all_products() {
    $products = [];
    $csvFile = __DIR__ . '/../src/wc-product-export-23-7-2026-1784835240835.csv';
    
    if (!file_exists($csvFile)) {
        return $products;
    }

    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        // En PHP 8.4, fgetcsv nécessite le paramètre d'échappement pour éviter les alertes de dépréciation.
        // On utilise "" comme escape pour respecter le standard CSV RFC 4180.
        $headers = fgetcsv($handle, 10000, ",", "\"", "");
        
        if ($headers === false) {
            fclose($handle);
            return $products;
        }

        $idCol = array_search('ID', $headers);
        $nameCol = array_search('Nom', $headers);
        $priceCol = array_search('Tarif régulier', $headers);
        if ($priceCol === false) $priceCol = array_search('Tarif promo', $headers);
        $descCol = array_search('Description courte', $headers);
        $imageCol = array_search('Images', $headers);
        $publishedCol = array_search('Publié', $headers);
        
        while (($data = fgetcsv($handle, 10000, ",", "\"", "")) !== FALSE) {
            if ($publishedCol !== false && isset($data[$publishedCol]) && $data[$publishedCol] == '0') {
                continue;
            }

            $id = isset($data[$idCol]) ? $data[$idCol] : uniqid();
            $title = isset($data[$nameCol]) ? $data[$nameCol] : 'Produit';
            
            // Correction de l'extraction du prix pour éviter les grands nombres si les colonnes sont décalées
            $rawPrice = isset($data[$priceCol]) ? $data[$priceCol] : '0';
            $price = 0.0;
            if (preg_match('/([0-9]+[.,]?[0-9]*)/', $rawPrice, $matches)) {
                $cleanPrice = str_replace(',', '.', $matches[1]);
                $price = (float)$cleanPrice;
            }

            $description = isset($data[$descCol]) ? $data[$descCol] : '';
            
            // Manage image path
            $localImage = '';
            if (isset($data[$imageCol]) && !empty($data[$imageCol])) {
                $imageUrls = explode(',', $data[$imageCol]);
                $firstUrl = trim($imageUrls[0]);
                $filename = basename(parse_url($firstUrl, PHP_URL_PATH));
                if (!empty($filename)) {
                    $localImage = '/public/imagesProduits/' . $filename;
                }
            }

            // Seulement ajouter les produits qui ont un ID et un Titre valides
            if (!empty($id) && !empty($title)) {
                $products[] = [
                    'id' => $id,
                    'title' => $title,
                    'price' => $price,
                    'image' => $localImage,
                    'description' => $description,
                ];
            }
        }
        fclose($handle);
    }

    return $products;
}

function get_product_by_id($id) {
    $products = get_all_products();
    foreach ($products as $p) {
        if ((string)$p['id'] === (string)$id) return $p;
    }
    return null;
}
?>
