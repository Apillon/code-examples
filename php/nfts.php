<?php
include './apillon.php';

// Function to create a new NFT collection
function createCollection($collectionData) {
  global $authorization;
  $url = "https://api.apillon.io/nfts/collections";

  $response = callAPI('POST', $url, $collectionData, $authorization);
  if (isset($response->error)) {
      echo "Error creating collection: " . $response->error;
      return null;
  }

  echo "New collection with UUID " . $response->data->collectionUuid . " created successfully!\n";
  return $response->data->collectionUuid;
}

// Function to mint an NFT for a collection
function mintNFT($collectionUuid, $mintData) {
  global $authorization;
  $url = "https://api.apillon.io/nfts/collections/$collectionUuid/mint";

  $response = callAPI('POST', $url, $mintData, $authorization);
  if (isset($response->error)) {
      echo "Error minting NFT: " . $response->error;
      return;
  }

  if ($response->status == 201) {
      echo "NFT minted successfully!\n";
  } else {
      echo "Failed to mint NFT.\n";
  }
}

// Example usage
$collectionData = [
  "chain" => 1287, // moonbase
  "collectionType" => 1, // generic
  "name" => 'Space Explorers',
  "description" => 'Space Explorers NFT collection',
  "symbol" => 'SPCE',
  "royaltiesFees" => 0,
  "royaltiesAddress" => '0x0000000000000000000000000000000000000000',
  "baseUri" => 'https://test.com/metadata/',
  "baseExtension" => '.json',
  "maxSupply" => 100,
  "isRevokable" => false,
  "isSoulbound" => false,
  "drop" => true,
  "dropStart" => 1687251003,
  "dropPrice" => 0.1,
  "dropReserve" => 5
];

$mintData = [
  "receivingAddress" => "0xdAC17F958D2ee523a2206206994597C13D831ec7",
  "quantity" => 1
];

$collectionUuid = createCollection($collectionData);
if ($collectionUuid) {
  mintNFT($collectionUuid, $mintData);
}