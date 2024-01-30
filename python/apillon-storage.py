import requests
import json
import os
from dotenv import load_dotenv
load_dotenv()

base_url = "https://api.apillon.io/storage/buckets"
headers = {
    "Authorization": f"Basic {os.getenv('API_KEY')}",
    "Content-Type": "application/json"
}

def pretty_print(response):
    print(json.dumps(response.json()['data'], indent=4))


def list_buckets():
    print("Listing Buckets...")
    response = requests.get(base_url, headers=headers)
    pretty_print(response)


def create_new_bucket():
    data = {
        "name": "My Bucket",
        "description": "Bucket for storing images"
    }
    response = requests.post(f"{base_url}", headers=headers, json=data)
    bucket_uuid = response.json()["data"]["bucketUuid"]
    print(f"New bucket with UUID {bucket_uuid} created successfully!")
    return bucket_uuid


def upload_to_bucket(bucket_uuid, file_name, file_path):
    data = {
        "files": [{
          "fileName": file_name,
          "contentType": "text/plain"
        }]
    }
    print("Uploading file...")
    response = requests.post(f"{base_url}/{bucket_uuid}/upload", headers=headers, json=data)
    upload_response_json = response.json()['data']

    # Find upload URL for the file corresponding to the upload file's name
    file_url = next(file["url"] for file in upload_response_json["files"] if file["fileName"] == file_name)

    # Send PUT request to the upload URL with the file's binary content
    with open(file_path, 'rb') as file:
        file_content = file.read()
        requests.put(file_url, data=file_content)

    # End upload session
    requests.post(f"{base_url}/{bucket_uuid}/upload/{upload_response_json['sessionUuid']}/end", headers=headers)
    print("File uploaded successfully!")


def list_bucket_content(bucket_uuid):
    response = requests.get(f"{base_url}/{bucket_uuid}/content", headers=headers)
    pretty_print(response)


bucket_uuid = create_new_bucket()
list_buckets()

file_path = os.path.join(os.path.dirname(__file__), '../files/', 'file-to-upload.txt')
upload_to_bucket(bucket_uuid, "file.txt", file_path)

list_bucket_content(bucket_uuid)