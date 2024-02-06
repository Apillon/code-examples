
##  Apillon API Examples

This README provides instructions on how to run the examples for interacting with the Apillon API using PHP, Python, and C#. Each example demonstrates how to perform operations such as creating a new bucket, listing buckets, uploading files to a bucket, and listing the content of a bucket.

### Prerequisites

Before running the examples, ensure you have the following prerequisites installed:

#### For PHP:

- PHP 7.4 or higher
- cURL extension enabled in PHP

#### For Python:

- Python 3.6 or higher
- requests library (Install using pip install requests)
- python-dotenv library (Install using pip install python-dotenv)
- Alternatively you can install locally with `pip3 install -r requirements.txt`

#### For C#:

- .NET Core 3.1 SDK or higher
- Newtonsoft.Json package (Install using dotnet add package Newtonsoft.Json)
- RestSharp package (Install using dotnet add package RestSharp)
- Alternatively install local nuget packages by running `dotnet restore`
- Ensure you have an IDE or editor that can run C# projects (e.g., Visual Studio, VS Code with C# extension, or JetBrains Rider).

**Additionally, you will need an API key from Apillon. Set this API key in your environment variables as API_KEY.**

### Running the Examples

#### PHP Example (apillon.php)

1. Open your terminal or command prompt.

2. Navigate to the directory containing the apillon.php file.

3. Before running the script, ensure you fill in the $apiKey, $apiSecret, and $bucketUuid variables in the script with your actual API key, secret, and bucket UUID.

4. Run the script using the PHP CLI:

```bash
php apillon.php
```

#### Python Example (apillon-storage.py)

1. Open your terminal or command prompt.

2. Navigate to the directory containing the apillon-storage.py file.

3. Ensure you have a .env file in the same directory with your API_KEY defined

```bash
API_KEY=your_api_key_here
```

5. Run the script using Python:

```bash
python3 apillon-storage.py
```

#### C# Example (Program.cs)
1. Ensure the Program.cs file is part of a .NET Core project. If not, create a new .NET Core Console App project and add the Program.cs file to it.

2. Open your terminal or command prompt.

3. Navigate to the project directory containing the Program.cs file.

4. Before running the program, ensure you have set the API_KEY environment variable. You can also directly assign your API key to the API_KEY variable in the code.

5. Run the project using the .NET CLI:

```bash
dotnet run Apillon-Storage.cs
```

**Notes**
- Ensure that the API key and other sensitive information are securely stored and not hard-coded in production environments.
- The examples provided are for demonstration purposes. You may need to modify them according to your specific requirements or API changes.