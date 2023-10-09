<?php
include '../apillon.php';

$result = getBucketContent();
if ($result !== null) {
    // Print out the total number of items in the bucket
    echo "Total items: " . $result['total'] . "\n";

    // Loop through each item and print out its details
    foreach ($result['items'] as $item) {
        echo "ID: " . $item->id . "\n";
        echo "Name: " . $item->name . "\n";
        echo "Type: " . ($item->type == 1 ? "Directory" : "File") . "\n";
        echo "Create Time: " . $item->createTime . "\n";
        echo "Update Time: " . $item->updateTime . "\n";

        // Only print these if it's a file
        if ($item->type == 2) {
            echo "Content Type: " . $item->contentType . "\n";
            echo "Size: " . $item->size . " bytes\n";
            echo "Parent Directory ID: " . $item->parentDirectoryId . "\n";
            echo "File UUID: " . $item->fileUuid . "\n";
            echo "CID: " . $item->CID . "\n";
            echo "Link: " . $item->link . "\n";
        }
    }
} else {
    echo "Failed to get bucket content.\n";
}