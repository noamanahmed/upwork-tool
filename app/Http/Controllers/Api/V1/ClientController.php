<?php

namespace App\Http\Controllers\Api\V1 ;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;

class ClientController extends BaseController
{
    public function __construct(
        private ClientService $clientService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->clientService->index();
    }



    /**
     * Display a listing of the resource.
     */
    public function dropdown()
    {
        return $this->clientService->dropdown();
    }


    /**
     * Display a listing of the resource.
     */
    public function dropdownForStatus()
    {
        return $this->clientService->dropdownForStatus();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        return $this->clientService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return $this->clientService->get($client->id);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        return $this->clientService->update($client->id,$request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        return $this->clientService->delete($client->id);
    }
}
