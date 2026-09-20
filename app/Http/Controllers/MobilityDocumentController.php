<?php

namespace App\Http\Controllers;

use App\Models\MobilityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MobilityDocumentController extends Controller
{
    public function download(Request $request, MobilityDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);
        abort_unless($document->storage_path && Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_filename ?: $document->document_type);
    }

    public function upload(Request $request, MobilityDocument $document): MobilityDocument
    {
        $this->authorize('upload', $document);
        $data = $request->validate(['file' => ['required', 'file', 'max:10240']]);
        $file = $data['file'];
        $path = $file->store("documentos/{$document->mobility_id}", 'private');

        $document->update([
            'storage_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'submitted_at' => now(),
            'validation_status' => MobilityDocument::STATUS_IN_REVIEW,
        ]);

        return $document->fresh();
    }

    public function validateDocument(Request $request, MobilityDocument $document): MobilityDocument
    {
        $this->authorize('validate', $document);
        $data = $request->validate([
            'validation_status' => ['required', 'in:validated,rejected,not_applicable'],
            'coordinator_observations' => ['nullable', 'string'],
        ]);

        $document->update([
            ...$data,
            'validated_at' => now(),
            'validated_by' => $request->user()->id,
        ]);

        return $document->fresh();
    }
}
