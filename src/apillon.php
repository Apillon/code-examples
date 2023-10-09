<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Retrieve API Credentials from environment variables
$apiKey = $_ENV['API_KEY'];
$apiSecret = $_ENV['API_SECRET'];
$authorization = $_ENV['AUTHORIZATION'];
$bucketUuid = $_ENV['BUCKET_UUID'];



// Function to perform HTTP requests
function toObject($array) {
    return json_decode(json_encode($array));
}

function callAPI($method, $url, $data = false, $authorization) {
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case "GET":
            if ($data !== null && (is_array($data) || is_object($data))) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
            break;
        default:
            break;
    }

    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $authorization,
        'Content-Type: application/json'
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    if (curl_error($curl)) {
        // Handle cURL error
        return toObject(['error' => curl_error($curl)]);
    }

    $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Handle HTTP error codes
    switch ($statusCode) {
        case 200:
        case 201:
            // Success or creation successful
            break;
        case 400:
            return toObject(['error' => 'Bad request. Check the request data and try again.']);
        case 401:
            return toObject(['error' => 'Unauthorized. Invalid API key or API key secret.']);
        case 403:
            return toObject(['error' => 'Forbidden. Insufficient permissions or unauthorized access to record.']);
        case 404:
            return toObject(['error' => 'Path not found. Invalid endpoint or resource.']);
        case 422:
            return toObject(['error' => 'Data validation failed. Invalid or missing fields.']);
        case 500:
            return toObject(['error' => 'Internal server error. Please try again later.']);
        default:
            return toObject(['error' => "Received HTTP code $statusCode"]);
    }

    $decodedResult = json_decode($result);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Handle JSON decode error
        return toObject(['error' => json_last_error_msg()]);
    }

    return toObject([
        'id' => $decodedResult->id ?? null,
        'status' => $statusCode,
        'data' => $decodedResult->data ?? null
    ]);
}

// Upload to bucket
function uploadToBucket($fileData, $path = '') {
    global $authorization, $bucketUuid;

    $responseArr = [
        'status' => '',
        'message' => '',
        'data' => []
    ];

    // Initialize file metadata array
    $files = [];
    foreach ($fileData as $data) {
        $files[] = [
            'fileName' => $data['fileName'],
            'contentType' => $data['contentType'],
            'path' => $path
        ];
    }

    // Validate if files array is empty
    if (empty($files)) {
        $responseArr['status'] = 'error';
        $responseArr['message'] = 'No files to upload.';
        return $responseArr;
    }

    // Prepare the POST request
    $url = "https://api.apillon.io/storage/$bucketUuid/upload";
    $postData = ['files' => $files];

    // Call API to start the upload session
    $response = callAPI('POST', $url, $postData, $authorization);

    // Check for errors in the API response
    if (isset($response->error)) {
        $responseArr['status'] = 'error';
        $responseArr['message'] = "API error: " . $response->error;
        return $responseArr;
    }

    if (isset($response->data->sessionUuid)) {
        // Extract session ID and upload URLs
        $sessionUuid = $response->data->sessionUuid;
        $uploadUrls = $response->data->files;

        // Initialize uploaded files array
        $uploadedFiles = [];

        // Perform file uploads
        foreach ($fileData as $index => $data) {
            $uploadUrl = $uploadUrls[$index]->url;
            $filePath = $data['filePath'];

            // Initialize cURL session for file upload
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_PUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_INFILE, fopen($filePath, 'r'));
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));
            
            // Execute the upload and close the session
            $uploadResponse = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (isset($uploadResponse['error'])) {
                $responseArr['status'] = 'error';
                $responseArr['message'] = "File upload error: " . $uploadResponse['error'];
                return $responseArr;
            }

            $uploadedFiles[] = $uploadResponse;
        }

        // End the upload session (Assuming you have an endUploadSession function)
        $endSession = endUploadSession($sessionUuid, $authorization, $bucketUuid);

        $responseArr['status'] = 'success';
        $responseArr['message'] = 'Files uploaded successfully';
        $responseArr['data'] = $uploadedFiles;

    } else {
        $responseArr['status'] = 'error';
        $responseArr['message'] = 'Failed to start upload session.';
    }

    return $responseArr;
}

// End upload session
function endUploadSession($sessionUuid, $authorization, $bucketUuid) {
    $url = "https://api.apillon.io/storage/$bucketUuid/upload/$sessionUuid/end";
    $data = ['directSync' => true];

    return callAPI('POST', $url, $data, $authorization);
}

// Get bucket content
function getBucketContent(
    $search = null, 
    $directoryId = null, 
    $page = null, 
    $limit = null, 
    $orderBy = null, 
    $desc = null
) {
    global $authorization, $bucketUuid;

    // Build query parameters
    $queryParams = [];
    if ($search !== null) $queryParams['search'] = $search;
    if ($directoryId !== null) $queryParams['directoryId'] = $directoryId;
    if ($page !== null) $queryParams['page'] = $page;
    if ($limit !== null) $queryParams['limit'] = $limit;
    if ($orderBy !== null) $queryParams['orderBy'] = $orderBy;
    if ($desc !== null) $queryParams['desc'] = $desc;

    $url = "https://api.apillon.io/storage/$bucketUuid/content";
    
    // Make the API call
    $response = callAPI('GET', $url, $queryParams, $authorization);
    
    // Check for errors
    if (isset($response->error)) {
        // Handle the error here
        echo "Error: " . $response->error;
        return null;
    }

    // Process the response
    $items = $response->data->items ?? [];
    $total = $response->data->total ?? 0;

    return [
        'items' => $items,
        'total' => $total
    ];
}


// Get file details
function getFileDetails($fileId, $authorization, $bucketUuid) {
    $url = "https://api.apillon.io/storage/$bucketUuid/file/$fileId/detail";

    return callAPI('GET', $url, false, $authorization);
}