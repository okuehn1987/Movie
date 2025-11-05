<?php

namespace App\Services;

use App\Events\ChatMessageDelta;
use App\Exceptions\OpenAIServerException;
use App\Models\ChatAssistant;
use App\Models\Chat;
use App\Models\ChatFile;
use Illuminate\Support\Facades\Log;
use OpenAI;
use App\Models\Organization;

class OpenAIService
{
    /** https://openai.com/pricing -> $ per 1000 tokens */

    private static $BASE_TOKEN_PRICE = ['input_token_price' => 0.001, 'output_token_price' => 0.002];
    private static $CURRENT_MODEL_TOKEN_PRICE = ['input_token_price' => 0.002, 'output_token_price' => 0.008];

    public static function getOrganizationClient(): OpenAI\Client
    {
        return OpenAI::client(Organization::getCurrent()->openai_api_key ?? env('OPENAI_API_KEY'));
    }

    public static function getCostForTokens(Int $tokens)
    {
        $markup = 1;
        return (1 + $markup) * $tokens * self::$BASE_TOKEN_PRICE['input_token_price'] / 1000;
    }

    public static function generateAIResponse(Chat $chat, string $msg, bool $retryLastRun)
    {
        $reachedLimit = OpenAIService::hasReachedTokenLimit($chat->chatAssistant, $chat->user);
        if ($reachedLimit)
            throw new OpenAIServerException('Der Service ist derzeit leider nicht verfügbar, wenden Sie sich bitte an den technischen Support.', 412, $reachedLimit);

        if (!Organization::getCurrent()->openai_api_key || env('MOCK_OPENAI_API')) {
            self::getMockResponse($chat, $msg);
        } else self::continueThreadResponsesAPI($chat, $msg, $retryLastRun);
    }

    public static function createAssistant(ChatAssistant $chatAssistant)
    {
        if (!Organization::getCurrent()->openai_api_key || env('MOCK_OPENAI_API')) {
            return 'mock-assistant-id';
        }

        self::uploadFiles($chatAssistant);

        $files = $chatAssistant->chatFiles;

        $vectorStore = self::getOrganizationClient()->vectorStores()->create([
            ...(count($files) > 0 ?
                ['file_ids' => $chatAssistant->chatFiles->pluck('assistant_api_file_id')->filter()->values()->all()] :
                []
            ),
            'name' => env('APP_NAME') . '-' . env('APP_ENV') . '#' . $chatAssistant->id,
        ]);

        $chatAssistant->vector_store_id = $vectorStore->id;
        $chatAssistant->save();

        return $vectorStore->id;
    }

    public static function updateAssistant(ChatAssistant $chatAssistant)
    {
        if (!Organization::getCurrent()->openai_api_key || env('MOCK_OPENAI_API')) return;

        self::uploadFiles($chatAssistant);

        if (!$chatAssistant->vector_store_id) {
            self::createAssistant($chatAssistant);
            return;
        }

        $vectorStoreId = $chatAssistant->vector_store_id;

        $current = self::getOrganizationClient()->vectorStores()->files()->list(
            vectorStoreId: $vectorStoreId,
            parameters: ['limit' => 100]
        );

        $currentIds = collect($current->data)->map(fn($f) => $f->id)->all();
        $wantedIds = $chatAssistant->chatFiles->pluck('assistant_api_file_id')->filter()->values()->all();

        foreach (array_diff($wantedIds, $currentIds) as $toAdd) {
            self::getOrganizationClient()->vectorStores()->files()->create(
                vectorStoreId: $vectorStoreId,
                parameters: ['file_id' => $toAdd],
            );
        }

        foreach (array_diff($currentIds, $wantedIds) as $toRemove) {
            self::getOrganizationClient()->vectorStores()->files()->delete(
                vectorStoreId: $vectorStoreId,
                fileId: $toRemove,
            );
        }
    }

    public static function startThreadAssistantAPI(Chat $chat)
    {
        if (!$chat->chatAssistant->vector_store_id)
            throw new OpenAIServerException("Der Assistant konnte nicht erstellt werden. Bitte versuchen Sie es später erneut.");

        $chat->chatMessages()->create([
            'msg' => "Hallo, ich bin ISA. Wie kann ich Ihnen helfen?",
            'role' => 'assistant',
            'assistant_api_message_id' => 'initialAssistantMessage-id'
        ]);

        $chat->last_response_id = null;
        $chat->save();
    }

    private static function continueThreadResponsesAPI(Chat $chat, string $msg, bool $retryLastRun)
    {
        if (!$chat->chatAssistant->vector_store_id) {
            throw new OpenAIServerException("Der Assistant konnte nicht erstellt werden. Bitte versuchen Sie es später erneut.");
        }


        $vectorStoreId = $chat->chatAssistant->vector_store_id;
        $previousId = $chat->last_response_id;

        $client = self::getOrganizationClient();

        if ($retryLastRun) {
            $lastUser = $chat->chatMessages()->where('role', 'user')->latest()->first();
            if ($lastUser && trim($lastUser->msg) !== '') {
                $msg = $lastUser->msg;
            }
        }

        $messages = [
            ['role' => 'system', 'content' => self::noSourceDisclosureRule()],
            ['role' => 'user', 'content' => $msg],
        ];

        $payload = [
            'model' => 'gpt-4.1',
            'input' => $messages,
            'instructions' => self::getInstructions(),
            'previous_response_id' => $previousId,
            'store' => true,
            'tools' => [
                [
                    'type' => 'file_search',
                    'vector_store_ids' => [$vectorStoreId],
                ],
            ]
        ];
        // repsonses sometimes doesnt exists on the hinted type, but its there. trust me!!
        $stream = $client->responses()->createStreamed($payload);

        $lastResponseId = null;
        $inputTokens = 0;
        $outputTokens = 0;
        $finalText = '';

        try {
            foreach ($stream as $event) {
                switch ($event->event ?? null) {
                    case 'response.created':
                        $lastResponseId = $event->response->id ?? null;
                        break;

                    case 'response.output_text.delta':
                        try {
                            $delta = $event->delta ?? ($event->data->delta ?? null);
                            if ($delta) event(new ChatMessageDelta($chat, $delta));
                        } catch (\Throwable $e) {
                            Log::error($e);
                        }
                        break;

                    case 'response.completed':
                        $resp = $event->response ?? null;
                        if ($resp) {
                            $lastResponseId = $resp->id ?? $lastResponseId;
                            $inputTokens   = $resp->usage->inputTokens  ?? $inputTokens;
                            $outputTokens  = $resp->usage->outputTokens ?? $outputTokens;
                            $finalText = $resp->outputText ?? $finalText;
                        }
                        break;

                    case 'response.error':
                        throw new OpenAIServerException("OpenAI ist zurzeit leider nicht erreichbar. Bitte versuchen Sie es später erneut.", 503, 'OPENAI_SERVER_ERROR');
                }
            }
        } catch (\Exception $e) {
            Log::error($e);
        }

        if ($lastResponseId) {
            $chat->last_response_id = $lastResponseId;
            $chat->save();
        }

        self::trackTokens($chat, (float)$inputTokens, (float)$outputTokens, "previous_chatMessage: {$chat->chatMessages()->latest()->first()->id}");

        if ($finalText !== '') {
            $chat->chatMessages()->create([
                'msg' => $finalText,
                'role' => 'assistant',
                'assistant_api_message_id' => $lastResponseId ?? null,
            ]);
            return;
        }

        throw new OpenAIServerException("OpenAI ist zurzeit leider nicht erreichbar. Bitte versuchen Sie es später erneut.", 503, 'OPENAI_SERVER_ERROR');
    }

    private static function trackTokens(Chat $chat, float $promptTokens, float $completionTokens, string $logMessage)
    {
        Log::info("Tracking tokens for chat $chat->id: promptTokens: $promptTokens, completionTokens: $completionTokens - $logMessage");

        $baseTokenPrice = self::$BASE_TOKEN_PRICE['input_token_price'];
        $promptTokenUsage = $promptTokens * self::$CURRENT_MODEL_TOKEN_PRICE['input_token_price'] / $baseTokenPrice;
        $completionTokenUsage = $completionTokens * self::$CURRENT_MODEL_TOKEN_PRICE['output_token_price'] / $baseTokenPrice;

        $chat->trackOpenAiTokensUsed($promptTokenUsage + $completionTokenUsage);
    }

    private static function noSourceDisclosureRule(): string
    {
        $org_name = Organization::getCurrent()->name;
        return <<<RULE
            HARTE REGEL: Du DARFST NIEMALS erwähnen oder andeuten,
            dass du Dokumente, Dateien, Unterlagen, PDFs, eine Wissensbasis,
            Retrieval, Suche, Tools, Vektorspeicher o.ä. nutzt.
            Vermeide strikt Formulierungen wie:
            "hochgeladene Datei(en)", "in den Dokumenten", "laut den Unterlagen",
            "ich habe nachgesehen", "mein Wissensspeicher", "Retrieval", "Vektorstore".
            Du musst aber trotzdem immer die bereitgestellten Materialien als Quelle nutzen und bei abweichendem Gesprächsverlauf charmant darauf zurücklenken.

            Wenn du etwas nicht sicher weißt, verweise höflich auf '$org_name' als Ansprechpartner."

            BEVOR du antwortest: Prüfe deine Ausgabe.
            Wenn sie eine Quelle/Datei/Tool erwähnt, formuliere sie um und lösche diese Hinweise.
        RULE;
    }

    public static function getInstructions()
    {
        $org_name = Organization::getCurrent()->name;
        $instructions =
            "Your name is ISA and you are a part of the 'team' of '$org_name'.\n" .
            "You are polite and helpful and answer questions that are asked by a user.\n" .
            "Use the material provided to you as a source.\n" .
            "The material provided to you can be used as a kind of FAQ.\n" .
            "Do not hallucinate and do not give false answers.\n" .
            "Do not cite details of other companies than '$org_name'.\n" .
            "Do not refer to other companies than '$org_name'.\n" .
            "If you want to provide information about '$org_name' always use file retrival to read that infromation from the provided files.\n" .
            "If you are not sure about an answer, or dont find the neccessary information, always refer to the 'team' of '$org_name'.\n" .
            "Prefer to refer to the 'team' of '$org_name' instead of other companies.\n" .
            "Answer questions in a way that is helpful, easy to understand, polite, professional, respectful, kind and courteous to the user.\n" .
            "The user you are talking with is a user of '$org_name'.\n" .
            "The user you are talking with did not upload any material and doesn't know about your source.\n" .
            "Do not mention the uploaded material to the user.\n" .
            "It is really important that you do not mention that you work with provided material or documents.\n" .
            "My job depends on you not mentioning that you work with provided material or documents.\n" .
            "If you can not answer a question, refer to the 'team' of '$org_name'.\n" .
            "The user you are talking with is not a member of the company '$org_name'.\n" .
            "Never use placeholders in answers, always run file retrival before providing information about '$org_name'\n" .
            "Answer always in the language used by the user, if there is no language detectable use german as a fallback language.\n";

        return $instructions;
    }

    private static function uploadFiles(ChatAssistant $chatAssistant)
    {

        foreach ($chatAssistant->loadMissing('chatFiles')->chatFiles as $file) {
            if (!$file->assistant_api_file_id) {
                $file->assistant_api_file_id = self::getOrganizationClient()->files()->upload([
                    'purpose' => 'assistants',
                    'file' => fopen(storage_path('app/' . $file->file_name), 'r'),
                ])->id;
                $file->save();
            }
        }
    }

    public static function deleteFile(ChatFile $file)
    {
        if (!Organization::getCurrent()->openai_api_key && env('MOCK_OPENAI_API')) return true;
        if (!$file->assistant_api_file_id) return true;

        try {
            $response = self::getOrganizationClient()->files()->delete($file->assistant_api_file_id);
        } catch (\Exception $e) {
            Log::error($e);
            throw new OpenAIServerException("Die Datei konnte nicht gelöscht werden. Bitte versuchen Sie es später erneut.");
        }
        return $response->deleted;
    }

    public static function getMockResponse(Chat $chat, string $msg)
    {
        $chat->trackOpenAiTokensUsed(random_int(1000, 10000));
        $chat->chatMessages()->create([
            'assistant_api_message_id' => 'mock-response-id' . uniqid(),
            'role' => 'assistant',
            'msg' => "Dies ist eine Mock-Antwort auf Ihre Nachricht: '$msg'",
        ]);
    }

    public static function hasReachedTokenLimit(ChatAssistant $chatAssistant)
    {
        if (self::getCostForTokens($chatAssistant->getTotalTokensForCurrentMonth()) >= $chatAssistant->monthly_cost_limit) {
            return true;
        }
        return false;
    }
}
