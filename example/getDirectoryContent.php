<?php
include '../apillon.php';

$filteredFiles = [];

// First, get the directory ID by searching for 'images'
$search = 'images';
$result = getBucketContent($search);

if ($result !== null && isset($result['items']) && count($result['items']) > 0) {
    
    // Check if the first matching item is a directory (type 1)
    if ($result['items'][0]->type == 1) {
        
        $directoryId = $result['items'][0]->id;

        // Now, get the files included in this directory
        $filesResult = getBucketContent(null, $directoryId);

        // Check if we got a result back and if any items are present
        if ($filesResult !== null && isset($filesResult['items']) && count($filesResult['items']) > 0) {
            
            foreach ($filesResult['items'] as $item) {
                if ($item->type == 2) { // type 2 means it's a file
                    // Save the file name and CID in the array
                    $filteredFiles[$item->name] = $item->CID;
                }
            }
        } else {
            echo "No files found in the directory with ID $directoryId.\n";
        }
        
    } else {
        echo "The item matching the search term '$search' is not a directory.\n";
    }

} else {
    echo "No items match the search term '$search' or failed to get bucket content.\n";
}