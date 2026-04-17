<?php

namespace App\Http\Controllers;

use App\Services\VoiceToSqlService;
use App\Support\Utils\LlmConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoiceToSqlController extends Controller
{
    protected $voiceToSqlService;

    public function __construct(VoiceToSqlService $voiceToSqlService)
    {
        $this->voiceToSqlService = $voiceToSqlService;
    }

    public function process(Request $request)
    {
        $request->validate([
            'provider' => ['required', function ($attr, $value, $fail) {
                if (!LlmConfig::isValidProvider($value)) {
                    $fail('Invalid provider');
                }
            }],

            'model' => ['required', function ($attr, $value, $fail) use ($request) {
                $provider = $request->input('provider');

                if (!LlmConfig::isValidModel($provider, $value)) {
                    $fail("Invalid model for provider [$provider]");
                }
            }],
            'text_query'    => 'nullable|string|max:5000',
            'audio_file'    => 'nullable|file|mimes:mp3,wav,webm,ogg|max:20480',
        ]);
        
        $response = $this->voiceToSqlService->handle($request);
        $data     = $response->getData(true);

        if ($response->getStatusCode() === 200) {
            return redirect()->back()->with('result', $data);
        }

        if ($response->getStatusCode() === 422) {
            return redirect()->back()->with('error', $data);  // separate key
        }

        return $response;
    }

    public function getPageData(Request $request)
    {
        $config = config('llm.llm_pricing');

        $aiProviders = [];

        foreach ($config as $provider => $models) {

            // Skip default block
            if ($provider === 'default') {
                continue;
            }

            foreach ($models as $model => $details) {

                $tier = $details['tier'] ?? 'default';

                // Map tier → badge (customize as you want)
                $badge = match ($tier) {
                    'ultra_cheap' => 'Ultra Cheap',
                    'cheap'       => 'Cheapest',
                    'balanced'    => 'Balanced',
                    'premium'     => 'Strong',
                    default       => 'Default',
                };

                $aiProviders[$provider][$model] = [
                    'badge' => $badge,
                ];
            }
        }

        return view('ai.sql-assitance', compact('aiProviders'));
    }

}