<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        return AuditLogResource::collection(
            AuditLog::latest()->paginate(20)
        );
    }

    public function show(AuditLog $auditLog)
    {
        return new AuditLogResource($auditLog);
    }
}