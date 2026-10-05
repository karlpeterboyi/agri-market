<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Farm;
use App\Services\Document\DocumentService;

class DocumentController extends Controller
{
    public function upload(
        StoreDocumentRequest $request,
        Farm $farm
    ) {
        $document = DocumentService::upload(
            $farm,
            $request->file('file'),
            $request->validated()
        );

        return new DocumentResource($document);
    }
}