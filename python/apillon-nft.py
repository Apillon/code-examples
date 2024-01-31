import requests
import json
import os
from dotenv import load_dotenv
load_dotenv()

base_url = "https://api.apillon.io/nfts/collections"
headers = {
    "Authorization": f"Basic {os.getenv('API_KEY')}",
    "Content-Type": "application/json"
}

def pretty_print(response):
    print(json.dumps(response.json()['data'], indent=4))

def get_collection(uuid):
    print(f"Getting collection {uuid}...")
    response = requests.get(f"{base_url}/{uuid}", headers=headers)
    pretty_print(response)

def list_collections():
    print("Listing collections...")
    response = requests.get(base_url, headers=headers)
    pretty_print(response)

def list_collection_transactions(uuid):
    print(f"Listing transactions for collection {uuid}...")
    response = requests.get(f"{base_url}/{uuid}/transactions", headers=headers)
    pretty_print(response)

def create_collection(collection_data):
    print("Creating new collection...")
    response = requests.post(base_url, headers=headers, json=collection_data)
    print(response.json())
    collection_uuid = response.json()["data"]["collectionUuid"]
    print(f"New collection with UUID {collection_uuid} created successfully!")
    return collection_uuid

def mint_nft(collection_uuid, mint_data):
    print(f"Minting NFT for collection {collection_uuid}...")
    response = requests.post(f"{base_url}/{collection_uuid}/mint", headers=headers, json=mint_data)
    print(response.json())
    if response.status_code == 201:
        print("NFT minted successfully!")
    else:
        print("Failed to mint NFT.")

# Example usage
collection_data = {
    "chain": 1287, # moonbase
    "collectionType": 1, # generic
    "name": 'Space Explorers',
    "description": 'Space Explorers NFT collection',
    "symbol": 'SPCE',
    "royaltiesFees": 0,
    "royaltiesAddress": '0x0000000000000000000000000000000000000000',
    "baseUri": 'https://test.com/metadata/',
    "baseExtension": '.json',
    "maxSupply": 100,
    "isRevokable": False,
    "isSoulbound": False,
    "drop": True,
    "dropStart": 1687251003,
    "dropPrice": 0.1,
    "dropReserve": 5
}

mint_data = {
    "receivingAddress": "0xdAC17F958D2ee523a2206206994597C13D831ec7",
    "quantity": 1
}

collection_uuid = create_collection(collection_data)
list_collections()
get_collection(collection_uuid)
list_collection_transactions(collection_uuid)
mint_nft(collection_uuid, mint_data)