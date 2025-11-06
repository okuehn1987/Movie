<?php

namespace App\Http\Controllers;

use App\Models\ChatAssistant;
use App\Models\Chat;
use App\Models\ChatFile;
use App\Models\ChatMessage;
use App\Models\Consumer;
use App\Models\Organization;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Laravel\Pennant\Feature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Container\Attributes\CurrentUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class IsaController extends Controller
{
    public function index()
    {
        $chatAssistant = Organization::getCurrent()->chatAssistant()->select(
            'id',
            'created_at',
            'updated_at',
            'monthly_cost_limit',
        )->with('chatFiles:id,chat_assistant_id')->first();

        if (!$chatAssistant) return back()->with('error', 'Dein Unternehmen hat noch keinen Chat Assistenten. Bitte kontaktiere den Support.');

        $monthTokens = $chatAssistant->chats()->where('created_at', '>=', Carbon::now()->startOfMonth())->sum('open_ai_tokens_used') ?? 0;
        $chatAssistant->current_monthly_cost = OpenAIService::getCostForTokens($monthTokens);

        return Inertia::render('Isa/IsaIndex', [
            'chatAssistant' => $chatAssistant,
            'files' => ChatFile::inOrganization()->get(['id', 'name', 'created_at']),
        ]);
    }

    public function update(Request $request, ChatAssistant $isa)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'monthly_cost_limit' => 'required|numeric|min:0',
            'chatFile_ids' => 'present|array',
            'chatFile_ids.*' => ['required', Rule::exists('chat_files', 'id')->where('organization_id', Organization::getCurrent()->id)],
        ]);

        $isa->update(['monthly_cost_limit' => $validated['monthly_cost_limit']]);

        $isa->chatFiles()
            ->whereNotIn('id', $validated['chatFile_ids'])
            ->update(['chat_assistant_id' => null]);

        ChatFile::inOrganization()
            ->whereIn('id', $validated['chatFile_ids'])
            ->update(['chat_assistant_id' => $isa->id]);


        OpenAIService::updateAssistant($isa);

        return back();
    }

    public function message(Request $request, #[CurrentUser] User $authUser)
    {
        $chat = $authUser->chats()->first();

        if ($chat == null) {
            $chat = Organization::getCurrent()->chatAssistant->chats()->create([
                'user_id' => $authUser->id,
            ]);
            OpenAIService::startThreadAssistantAPI($chat);
        }

        $validated = $request->validate([
            'msg' => 'required|string|max:2000',
        ]);

        $chat->sendUserMessage(str_replace('\n', '<br/>', $validated['msg']), false);

        return back();
    }

    public function deleteChat(Chat $chat, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        if ($chat->user_id === $authUser->id) {
            $chat->chatMessages()->delete();
            $chat->delete();
        };

        return back();
    }

    public function retryLastRun(Chat $chat)
    {
        $chat->sendUserMessage('', true);
        return back();
    }
}
