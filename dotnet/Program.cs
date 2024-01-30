using System;
using System.IO;
using System.Net;
using System.Text;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using RestSharp;
using RestSharp.Authenticators;
using System.Net.Http;

class Program
{
    // Create a RestClient instance with the base URL of the API
    private static readonly RestClient client = new RestClient("https://api.apillon.io/storage/buckets");
    // Get the API key from the environment variables
    private static readonly string API_KEY = Environment.GetEnvironmentVariable("API_KEY");

    static async Task Main()
    {
        client.AddDefaultHeader("Authorization", $"Basic {API_KEY}");

        var bucket_uuid = await CreateNewBucket();
        await ListBuckets();

        // Define the path of the file to be uploaded
        var file_path = Path.Combine(Directory.GetCurrentDirectory(), "../files/", "file-to-upload.txt");
        await UploadToBucket(bucket_uuid, "file.txt", file_path);

        await ListBucketContent(bucket_uuid);
    }

    // Pretty print the data from the response
    static async Task PrettyPrint(IRestResponse response)
    {
        var parsedJson = JObject.Parse(response.Content);
        Console.WriteLine(JsonConvert.SerializeObject(parsedJson["data"], Formatting.Indented));
    }

    static async Task ListBuckets()
    {
        Console.WriteLine("Listing Buckets...");
        var request = new RestRequest(Method.GET);
        var response = await client.ExecuteAsync(request);
        await PrettyPrint(response);
    }

    static async Task<string> CreateNewBucket()
    {
        var data = new
        {
            name = "My Bucket",
            description = "Bucket for storing images"
        };
        var request = new RestRequest(Method.POST);
        request.AddJsonBody(data);
        var response = await client.ExecuteAsync(request);
        Console.WriteLine(response.Content);
        var bucket_uuid = JObject.Parse(response.Content)["data"]["bucketUuid"].ToString();
        Console.WriteLine($"New bucket with UUID {bucket_uuid} created successfully!");
        return bucket_uuid;
    }

    static async Task UploadToBucket(string bucket_uuid, string file_name, string file_path)
    {
        var data = new
        {
            files = new[] {
                new {
                    fileName = file_name,
                    contentType = "text/plain"
                }
            }
        };
        Console.WriteLine("Uploading file...");
        var request = new RestRequest($"{bucket_uuid}/upload", Method.POST);
        request.AddJsonBody(data);
        var response = await client.ExecuteAsync(request);
        var upload_response_json = JObject.Parse(response.Content)["data"];

        // Get the upload URL from the response
        var file_url = upload_response_json["files"].First["url"].ToString();

        // Read the file as binary data
        var file_content = File.ReadAllBytes(file_path);
        // Create a PUT request with the upload URL
        var uploadRequest = new HttpRequestMessage(HttpMethod.Put, file_url);
        uploadRequest.Content = new ByteArrayContent(file_content);
        uploadRequest.Content.Headers.ContentType = new System.Net.Http.Headers.MediaTypeHeaderValue("application/octet-stream");
        var uploadResponse = await new HttpClient().SendAsync(uploadRequest);

        // End the upload session
        var endUploadRequest = new RestRequest($"{bucket_uuid}/upload/{upload_response_json["sessionUuid"]}/end", Method.POST);
        await client.ExecuteAsync(endUploadRequest);
        Console.WriteLine("File uploaded successfully!");
    }

    static async Task ListBucketContent(string bucket_uuid)
    {
        var request = new RestRequest($"{bucket_uuid}/content", Method.GET);
        var response = await client.ExecuteAsync(request);
        await PrettyPrint(response);
    }
}