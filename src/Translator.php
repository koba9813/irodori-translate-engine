<?php

namespace Irodori\Honyaku;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Translator Class
 *
 * A PHP wrapper for the Sakura AI translation API. It handles building the request,
 * sending it to the API, and parsing the response.
 */
class Translator
{
    /** @var string The default API endpoint for the translation service. */
    private const DEFAULT_API_ENDPOINT = 'https://api.ai.sakura.ad.jp/v1/chat/completions';
    /** @var int The maximum allowed length for the input text in characters. */
    private const MAX_TEXT_LENGTH = 1500;
    /** @var int The timeout for the cURL request in seconds. */
    private const CURL_TIMEOUT = 60;
    /** @var int The maximum number of retries for a failed request. */
    private const MAX_RETRIES = 2;

    /** @var string The API key for authenticating with the translation service. */
    private string $apiKey;
    /** @var string The API endpoint URL. */
    private string $apiEndpoint;

    /**
     * Translator constructor.
     *
     * @param string      $apiKey      The API key for the translation service.
     * @param string|null $apiEndpoint Optional. The API endpoint URL. Defaults to DEFAULT_API_ENDPOINT.
     * @throws InvalidArgumentException if the API key is empty.
     */
    public function __construct(string $apiKey, ?string $apiEndpoint = null)
    {
        if (empty($apiKey)) {
            throw new InvalidArgumentException('API key cannot be empty.');
        }
        $this->apiKey = $apiKey;
        $this->apiEndpoint = $apiEndpoint ?? self::DEFAULT_API_ENDPOINT;
    }

    /**
     * Translates the given text based on the provided options.
     *
     * @param array $options An associative array of translation options.
     *                       See README.md for available keys.
     * @return array An associative array containing the translation result.
     * @throws InvalidArgumentException on invalid input.
     * @throws RuntimeException on API communication or response parsing errors.
     */
    public function translate(array $options): array
    {
        $this->validateOptions($options);

        $inputText = trim($options['text']);
        $targetLang = $options['target'] ?? 'english';

        $messages = $this->buildMessages($inputText, $targetLang, $options);
        
        $apiRequestBody = [
            'model' => 'gpt-oss-120b',
            'messages' => $messages,
            'temperature' => 0.1,
            'max_tokens' => 5000
        ];

        $apiResponse = $this->executeCurl($apiRequestBody);

        return $this->parseResponse($apiResponse);
    }

    /**
     * Validates the provided translation options.
     *
     * @param array $options The translation options.
     * @throws InvalidArgumentException if validation fails.
     */
    private function validateOptions(array $options): void
    {
        if (!isset($options['text']) || empty(trim($options['text']))) {
            throw new InvalidArgumentException('Input text cannot be empty.');
        }

        if (mb_strlen($options['text']) > self::MAX_TEXT_LENGTH) {
            throw new InvalidArgumentException('Input text is too long. Max ' . self::MAX_TEXT_LENGTH . ' characters.');
        }
    }

    /**
     * Builds the message array to be sent to the AI API.
     *
     * @param string $inputText The user's input text.
     * @param string $targetLang The target language.
     * @param array $options The full translation options.
     * @return array The array of messages.
     */
    private function buildMessages(string $inputText, string $targetLang, array $options): array
    {
        $sourceLang = $options['source'] ?? 'auto';
        $finalSystemPrompt = $this->buildSystemPrompt($targetLang, $sourceLang, $options);

        $messages = [['role' => 'system', 'content' => $finalSystemPrompt]];

        // Add a one-shot example for non-Japanese targets to guide the AI's output format.
        if ($targetLang !== 'japanese') {
            $messages[] = ['role' => 'user', 'content' => 'ありがとう'];
            $messages[] = ['role' => 'assistant', 'content' => '{"translation": "Thank you", "katakana": "テン キュー", "ruby_text": "Thank{テン} you{キュー}"}'];
        }
        $messages[] = ['role' => 'user', 'content' => $inputText];

        return $messages;
    }

    /**
     * Builds the system prompt string based on translation options.
     *
     * @param string $targetLang The target language.
     * @param string $sourceLang The source language.
     * @param array $options The full translation options.
     * @return string The complete system prompt.
     */
    private function buildSystemPrompt(string $targetLang, string $sourceLang, array $options): string
    {
        $style = $options['style'] ?? 'standard';
        $customPrompt = $options['custom_prompt'] ?? '';
        $isLiteral = $options['is_literal'] ?? false;

        $langNames = [
            'auto' => 'Autodetect', 'japanese' => 'Japanese', 'english' => 'English',
            'french' => 'French', 'korean' => 'Korean', 'chinese' => 'Chinese'
        ];
        $sourceLangName = $langNames[$sourceLang] ?? ucfirst($sourceLang);
        $targetLangName = $langNames[$targetLang] ?? ucfirst($targetLang);

        $modeInstruction = $isLiteral
            ? "Priority: Strict literal translation (choku-yaku / 直訳). Maintain source structure and nuances exactly, even if it sounds unnatural."
            : "Priority: Natural translation (i-yaku / 意訳). Focus on flow, common expressions, and context-appropriate vocabulary.";

        $styleInstruction = $this->getStyleInstruction($targetLang, $style, $customPrompt);
        
        $systemPrompt = ($sourceLang === 'auto')
            ? "You are a language detection and translation API. First, identify the language of the user's text. Then, translate it to {$targetLangName}."
            : "You are a {$sourceLangName}-to-{$targetLangName} translator API.";

        $outputInstruction = $this->getOutputInstruction($targetLang, $sourceLang);

        return implode("\n", array_filter([
            $systemPrompt,
            $outputInstruction,
            $modeInstruction,
            $styleInstruction
        ]));
    }
    
    /**
     * Gets the style instruction string based on the target language and style.
     *
     * @param string $targetLang The target language.
     * @param string $style The selected style.
     * @param string $customPrompt The custom prompt text.
     * @return string The style instruction.
     */
    private function getStyleInstruction(string $targetLang, string $style, string $customPrompt): string
    {
        if ($style === 'custom' && !empty($customPrompt)) {
            return "Special Instruction: " . $customPrompt;
        }

        $styleMap = [];
        if ($targetLang === 'japanese') {
            $styleMap = ['casual' => 'Use casual, informal Japanese (tameguchi).', 'polite' => 'Use formal, polite Japanese (keigo).', 'academic' => 'Use academic, objective Japanese (da/dearu style).', 'kansai' => 'Use Kansai dialect (Kansai-ben).'];
            return $styleMap[$style] ?? 'Use standard Japanese.';
        } elseif ($targetLang === 'english') {
            $styleMap = ['american' => 'Use American English (US) spelling and idioms.', 'british' => 'Use British English (UK) spelling and idioms.', 'middle_school' => 'Use simple English suitable for a Japanese junior high school student.'];
            return $styleMap[$style] ?? '';
        } elseif ($targetLang === 'chinese') {
            return ($style === 'traditional') ? 'Use Traditional Chinese characters (繁體字).' : 'Use Simplified Chinese characters (簡体字).';
        }
        return '';
    }

    /**
     * Gets the output format instruction string for the AI.
     *
     * @param string $targetLang The target language.
     * @param string $sourceLang The source language.
     * @return string The output instruction.
     */
    private function getOutputInstruction(string $targetLang, string $sourceLang): string
    {
        if ($targetLang === 'japanese') {
            if ($sourceLang === 'auto') {
                return "Output ONLY a valid JSON object with 'detected_source' and 'translation' keys. {\"detected_source\": \"detected language id (e.g., english)\", \"translation\": \"...\"}";
            }
            return "Output ONLY a valid JSON object with a 'translation' key. {\"translation\": \"...\"}";
        }
        return "Output ONLY a JSON object. NO THINKING. NO EXPLANATION. JSON keys: 'translation', 'katakana', 'ruby_text'. 'ruby_text' format: word{pronunciation}. Output JSON as a SINGLE LINE.";
    }

    /**
     * Executes the cURL request to the translation API.
     *
     * @param array $apiRequestBody The body of the API request.
     * @return string The raw response body from the API.
     * @throws RuntimeException if the request fails.
     */
    private function executeCurl(array $apiRequestBody): string
    {
        $ch = curl_init($this->apiEndpoint);
        $authHeader = 'Authorization: Basic ' . base64_encode($this->apiKey);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($apiRequestBody),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', $authHeader],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => self::CURL_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = false;
        $retryCount = 0;
        while ($retryCount < self::MAX_RETRIES && $response === false) {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            
            if ($response === false) {
                if (strpos($curlError, 'timeout') !== false) {
                    $retryCount++;
                    if ($retryCount < self::MAX_RETRIES) {
                        usleep(500000);
                        continue;
                    }
                }
                curl_close($ch);
                throw new RuntimeException('Failed to communicate with translation service: ' . $curlError);
            }
            
            if ($httpCode !== 200) {
                curl_close($ch);
                throw new RuntimeException('Translation service returned an error. HTTP Code: ' . $httpCode);
            }
            break;
        }
        
        curl_close($ch);
        return $response;
    }

    /**
     * Parses the raw JSON response from the API.
     *
     * @param string $responseBody The raw JSON string from the API.
     * @return array The parsed translation data.
     * @throws RuntimeException if parsing fails or the response is invalid.
     */
    private function parseResponse(string $responseBody): array
    {
        $apiResponse = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to parse API response JSON.');
        }

        $message = $apiResponse['choices'][0]['message'] ?? null;
        if (!$message || (empty($message['content']) && empty($message['reasoning_content']))) {
             throw new RuntimeException('AI response was empty.');
        }

        $aiContent = trim($message['content'] ?? $message['reasoning_content']);
        $jsonString = $this->extractJsonFromString($aiContent);
        
        $translationData = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($translationData['translation'])) {
            throw new RuntimeException('Failed to parse translation data from AI content. Raw preview: ' . mb_substr($jsonString, 0, 500));
        }

        return $translationData;
    }
    
    /**
     * Extracts a JSON object from a string, which might be wrapped in other text.
     *
     * @param string $str The input string.
     * @return string The extracted JSON string.
     */
    private function extractJsonFromString(string $str): string
    {
        // Match a JSON object that might be wrapped in markdown code blocks
        if (preg_match('/```json\s*(\{[\s\S]*\})\s*```/', $str, $matches)) {
            return $matches[1];
        }
        // Match a JSON object that is the main content of the string
        if (preg_match('/^\s*(\{[\s\S]*\})\s*$/', $str, $matches)) {
            return $matches[1];
        }
        // Fallback to find the first JSON object in the string
        if (preg_match('/(\{[\s\S]*\})/', $str, $matches)) {
            return $matches[1];
        }
        return $str;
    }
}