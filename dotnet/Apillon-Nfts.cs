using System;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using RestSharp;
using System.Threading.Tasks;
using System.Collections.Generic;

class ApillonNfts
{
    private static readonly RestClient client = new RestClient("https://api.apillon.io/nfts/collections");
    private static readonly string API_KEY = Environment.GetEnvironmentVariable("API_KEY");

    static async Task Main()
    {
        client.AddDefaultHeader("Authorization", $"Basic {API_KEY}");
        client.AddDefaultHeader("Content-Type", "application/json");

        var collectionData = new Dictionary<string, object>
        {
            {"chain", 1287},
            {"collectionType", 1},
            {"name", "Space Explorers"},
            {"description", "Space Explorers NFT collection"},
            {"symbol", "SPCE"},
            {"royaltiesFees", 0},
            {"royaltiesAddress", "0x0000000000000000000000000000000000000000"},
            {"baseUri", "https://test.com/metadata/"},
            {"baseExtension", ".json"},
            {"maxSupply", 100},
            {"isRevokable", false},
            {"isSoulbound", false},
            {"drop", true},
            {"dropStart", 1687251003},
            {"dropPrice", 0.1},
            {"dropReserve", 5}
        };

        var mintData = new Dictionary<string, object>
        {
            {"receivingAddress", "0xdAC17F958D2ee523a2206206994597C13D831ec7"},
            {"quantity", 1}
        };

        var collectionUuid = await CreateCollection(collectionData);
        await ListCollections();
        await GetCollection(collectionUuid);
        await ListCollectionTransactions(collectionUuid);
        await MintNft(collectionUuid, mintData);
    }

    static void PrettyPrint(IRestResponse response)
    {
        var parsedJson = JObject.Parse(response.Content);
        Console.WriteLine(JsonConvert.SerializeObject(parsedJson["data"], Formatting.Indented));
    }

    static async Task ListCollections()
    {
        Console.WriteLine("Listing collections...");
        var request = new RestRequest(Method.GET);
        var response = await client.ExecuteAsync(request);
        PrettyPrint(response);
    }

    static async Task<string> CreateCollection(Dictionary<string, object> collectionData)
    {
        Console.WriteLine("Creating new collection...");
        var request = new RestRequest(Method.POST);
        request.AddJsonBody(collectionData);
        var response = await client.ExecuteAsync(request);
        Console.WriteLine(response.Content);
        var collectionUuid = JObject.Parse(response.Content)["data"]["collectionUuid"].ToString();
        Console.WriteLine($"New collection with UUID {collectionUuid} created successfully!");
        return collectionUuid;
    }

    static async Task GetCollection(string uuid)
    {
        Console.WriteLine($"Getting collection {uuid}...");
        var request = new RestRequest($"{uuid}", Method.GET);
        var response = await client.ExecuteAsync(request);
        PrettyPrint(response);
    }

    static async Task ListCollectionTransactions(string uuid)
    {
        Console.WriteLine($"Listing transactions for collection {uuid}...");
        var request = new RestRequest($"{uuid}/transactions", Method.GET);
        var response = await client.ExecuteAsync(request);
        PrettyPrint(response);
    }

    static async Task MintNft(string collectionUuid, Dictionary<string, object> mintData)
    {
        Console.WriteLine($"Minting NFT for collection {collectionUuid}...");
        var request = new RestRequest($"{collectionUuid}/mint", Method.POST);
        request.AddJsonBody(mintData);
        var response = await client.ExecuteAsync(request);
        Console.WriteLine(response.Content);
        if (response.StatusCode == System.Net.HttpStatusCode.Created)
        {
            Console.WriteLine("NFT minted successfully!");
        }
        else
        {
            Console.WriteLine("Failed to mint NFT.");
        }
    }
}