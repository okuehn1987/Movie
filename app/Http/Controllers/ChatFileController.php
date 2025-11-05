<?php

namespace App\Http\Controllers;

use App\Models\ChatFile;
use Illuminate\Http\Request;
use App\Models\Organization;
use Inertia\Inertia;
use App\Models\File;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatFileController extends Controller
{

    public function store(Request $request, #[CurrentUser] User $user)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|nullable|mimes:pdf|max:100000',
        ]);

        $errors = [];

        foreach ($validated['files'] as $file) {
            if (ChatFile::where('name', $file->getClientOriginalName())->inOrganization()->get()->isNotEmpty())
                array_push($errors, $file->getClientOriginalName() . ': Die Datei existiert bereits.');

            else {
                $path = $file->store('files');
                ChatFile::create([
                    'name' => $file->getClientOriginalName(),
                    'file_name' => $path,
                    'organization_id' => $user->organization_id,
                ]);

                // Organization::getCurrent()->chatAssistant->chatFiles()->create([
                //     'name' => $file->getClientOriginalName(),
                //     'file_name' => $path,
                //     'organization_id' => $user->organization_id,
                // ]);
            }
        }

        return back()->withErrors($errors);
    }

    public function destroy(ChatFile $file)
    {
        if ($file->chat_assistant_id != null) {
            return back()->withErrors(['file is in use']);
        };
        if (OpenAIService::deleteFile($file)) {
            $file->delete();
            Storage::delete($file->file_name);
        }

        return redirect()->route('isa.index');
    }

    public function show(ChatFile $file)
    {
        return Inertia::render('Admin/File/FileShow', [
            'file' => $file,
        ]);
    }
}
