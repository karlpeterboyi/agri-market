<?php

namespace App\Services\Document;

use App\Models\Document;
use Illuminate\Http\UploadedFile;

class DocumentService
{
    public static function upload(
        $model,
        UploadedFile $file,
        array $data = []
    ): Document {

        $path = $file->store(
            'documents',
            'public'
        );

        return Document::create([

            'organisation_id' => $model->organisation_id ?? null,

            'uploaded_by' => auth()->id(),

            'documentable_type' => get_class($model),

            'documentable_id' => $model->id,

            'name' => pathinfo(
                $file->hashName(),
                PATHINFO_FILENAME
            ),

            'original_name' => $file->getClientOriginalName(),

            'disk' => 'public',

            'path' => $path,

            'mime_type' => $file->getMimeType(),

            'size' => $file->getSize(),

            'category' => $data['category'] ?? null,

            'visibility' => $data['visibility'] ?? 'private',

        ]);

    }
}