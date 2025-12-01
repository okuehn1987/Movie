<?php

namespace App\Http\Controllers;

use App\Models\ChatAssistant;
use App\Models\ChatFile;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChatFileController extends Controller
{

    public function store(Request $request, #[CurrentUser] User $user)
    {
        Gate::authorize('editChatFiles', ChatAssistant::class);

        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|nullable|mimes:pdf|max:100000',
        ]);

        $errors = [];

        foreach ($validated['files'] as $file) {
            if (ChatFile::inOrganization()->where('name', $file->getClientOriginalName())->exists())
                array_push($errors, $file->getClientOriginalName() . ': Die Datei existiert bereits.');
            else {
                $path = $file ? Storage::disk('chat_files')->putFile($file) : null;
                ChatFile::create([
                    'name' => $file->getClientOriginalName(),
                    'file_name' => $path,
                    'organization_id' => $user->organization_id,
                ]);
            }
        }

        return back()->withErrors($errors);
    }

    public function destroy(ChatFile $file)
    {
        Gate::authorize('editChatFiles', ChatAssistant::class);

        if ($file->chat_assistant_id != null) {
            return back()->withErrors(['file is in use']);
        };
        if (OpenAIService::deleteFile($file)) {
            Storage::disk('chat_files')->delete($file->file_name);
            $file->delete();
        }

        return back();
    }

    public function show(ChatFile $file)
    {
        Gate::authorize('viewIndex', [ChatAssistant::class]);

        return Inertia::render('Isa/FileShow', [
            'file' => $file,
        ]);
    }
}
