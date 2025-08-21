<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ImportCsvJob;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $file = $request->validate(['csv' => 'required|file|mimes:csv,txt|max:20480'])['csv'];
        $path = $file->store('uploads');

        ImportCsvJob::dispatch(auth()->id(), $path);
        return back()->with('success', 'Arquivo enviado! Processamento iniciado.');
    }
}
