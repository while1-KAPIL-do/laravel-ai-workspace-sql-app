<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SchemaService;

class SchemaController extends Controller
{
    public function __construct(
        protected SchemaService $service
    ) {}

    public function uploadSchema(Request $request)
    {

        $request->validate(
            [
                'schema' => 'required|file|mimes:sql,txt|max:2048',
            ],
            [
                'schema.required' => 'Please upload a schema file.',
                'schema.file'     => 'Uploaded item must be a valid file.',
                'schema.mimes'    => 'Only .sql or .txt files are allowed.',
                'schema.max'      => 'File size must not exceed 2MB.',
            ]
        );

        try {
            return $this->service->uploadSchema($request);

        } catch (\Throwable $e) {
            Log::error("Schema Upload Error: " . $e->getMessage());
            return response()->json([
                'error' => 'Upload failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function dbSchema(Request $request)
    {
        return $this->service->dbSchema();
    }


    public function executeSql(Request $request)
    {
        try {
            return $this->service->executeSql($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}