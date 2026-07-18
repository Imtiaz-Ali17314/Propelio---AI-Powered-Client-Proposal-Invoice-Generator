<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * List the authenticated user's clients (with optional search).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = $request->user()
            ->clients()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount(['proposals', 'invoices'])
            ->latest()
            ->paginate(10);

        return response()->json($clients);
    }

    public function store(ClientRequest $request)
    {
        $this->authorize('create', Client::class);

        $client = $request->user()->clients()->create($request->validated());

        return response()->json([
            'message' => 'Client created successfully.',
            'client' => $client,
        ], 201);
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        return response()->json($client->load(['proposals', 'invoices']));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return response()->json([
            'message' => 'Client updated successfully.',
            'client' => $client,
        ]);
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully.']);
    }
}