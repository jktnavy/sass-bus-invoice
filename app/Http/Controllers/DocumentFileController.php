<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentFileController extends Controller
{
    public function show(Request $request, string $id)
    {
        $document = $this->resolveDocument($request, $id);
        $storagePath = $document->storage_path ?: $document->path;

        if (! $storagePath || ! Storage::disk('local')->exists($storagePath)) {
            abort(404, 'File not found.');
        }

        return response()->file(
            Storage::disk('local')->path($storagePath),
            [
                'Content-Type' => $document->mime,
                'Content-Disposition' => 'inline; filename="'.$document->filename.'"',
            ],
        );
    }

    public function download(Request $request, string $id)
    {
        $document = $this->resolveDocument($request, $id);
        $storagePath = $document->storage_path ?: $document->path;

        if (! $storagePath || ! Storage::disk('local')->exists($storagePath)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($storagePath, $document->filename);
    }

    private function resolveDocument(Request $request, string $id): Document
    {
        $user = $request->user();

        abort_unless($user, 403);

        $query = Document::query()->where('id', $id);

        if ($user->role !== 'superadmin') {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query->firstOrFail();
    }
}
