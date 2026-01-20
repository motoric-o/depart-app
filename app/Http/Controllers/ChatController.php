<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\Route;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function handle(Request $request)
    {
        $message = $request->input('message');
        
        // Check if API key is configured
        $apiKey = env('GEMINI_API_KEY');

        if ($apiKey) {
            try {
                $response = $this->callGeminiAPI($message, $apiKey);
            } catch (\Exception $e) {
                // Fallback on error
                $response = $this->generateRuleBasedResponse(strtolower($message));
            }
        } else {
            $response = $this->generateRuleBasedResponse(strtolower($message));
        }

        return response()->json([
            'response' => $response
        ]);
    }

    private function callGeminiAPI($userMessage, $apiKey)
    {
        // Gather Context
        $destinations = Destination::pluck('city_name')->implode(', ');
        $routesQuery = Route::with('destination')->take(10)->get();
        $routes = $routesQuery->map(function($r) {
            return "From {$r->source} to {$r->destination->city_name} ($r->distance km)";
        })->implode('; ');

        $systemPrompt = "You are a friendly virtual travel assistant for 'Depart App', a bus ticketing platform in Indonesia.
        
        Here is the context of our service:
        - We offer inter-city and inter-provincial bus tickets.
        - Payment methods: Bank Transfer, E-Wallet (OVO, Dana, GoPay), Minimarket.
        - Refund: Available 24 hours before departure with a 25% administration fee.
        - Support: 24/7 at 0800-1234-5678 or support@departapp.com.

        Available destinations in database: {$destinations}.
        Example Routes: {$routes}.
        
        Your Goal: Answer user questions concisely in English. If they ask for recommendations, suggest destinations from our list and explain why they are interesting (make it fun based on general knowledge). If they ask about booking, guide them to use the search bar on the home page. Use a professional yet warm tone. Format: Use HTML tags like <b>, <strong>, <br> for formatting.";

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nUser: " . $userMessage . "\nAssistant:"]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                throw new \Exception('API Request Failed');
            }

            $data = $response->json();

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $data['candidates'][0]['content']['parts'][0]['text'];
                $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
                $text = nl2br($text);
                return $text;
            }
        } catch (\Exception $e) {
            Log::error('Gemini Connection Error: ' . $e->getMessage());
            throw $e;
        }

        throw new \Exception('Invalid API response');
    }

    private function generateRuleBasedResponse($message)
    {
        // Greetings
        if (preg_match('/\b(hi|hello|hey|halo|hai|selamat pagi|selamat siang|selamat sore|selamat malam)\b/', $message)) {
            return "Hello! Welcome to " . config('app.name') . ". I can help you with ticket info, refunds, or travel recommendations. How can I assist you?";
        }

        // FAQs
        if (str_contains($message, 'book') || str_contains($message, 'ticket') || str_contains($message, 'reservation') || str_contains($message, 'pesan') || str_contains($message, 'tiket') || str_contains($message, 'beli')) {
            return "To book a ticket, please use the search bar on the homepage. Enter origin, destination, and date, then click 'Search Bus'.";
        }

        if (str_contains($message, 'refund') || str_contains($message, 'cancel') || str_contains($message, 'batal') || str_contains($message, 'kembali')) {
            return "Refunds are available if cancellation is made at least 24 hours before departure. A 25% administration fee applies.";
        }

        if (str_contains($message, 'pay') || str_contains($message, 'method') || str_contains($message, 'bayar')) {
            return "We accept Bank Transfer, E-Wallet (OVO, Dana, GoPay), and payments at minimarkets like Indomaret/Alfamart.";
        }

        if (str_contains($message, 'contact') || str_contains($message, 'support') || str_contains($message, 'help') || str_contains($message, 'kontak') || str_contains($message, 'bantuan') || str_contains($message, 'cs')) {
            return "Our customer support is available 24/7. Contact us at 0800-1234-5678 or email support@departapp.com.";
        }

        // Tour Recommendations
        if (str_contains($message, 'recommend') || str_contains($message, 'tour') || str_contains($message, 'visit') || str_contains($message, 'suggest') || str_contains($message, 'trip') || str_contains($message, 'saran') || str_contains($message, 'wisata') || str_contains($message, 'jalan')) {
            return $this->getRecommendations();
        }

        // Default
        return "Sorry, I didn't quite get that. You can ask about booking tickets, refund policies, payment methods, or travel recommendations!";
    }

    private function getRecommendations()
    {
        // Get 3 random destinations that have routes
        $destinations = Destination::whereHas('routes')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        if ($destinations->isEmpty()) {
            return "We serve many beautiful cities across the country! Check popular routes on our homepage.";
        }

        $response = "Here are some interesting destinations you might like:<br><br>";
        
        foreach ($destinations as $dest) {
            $response .= "<strong>" . $dest->city_name . "</strong>: An amazing place to visit! <br>";
        }

        $response .= "<br>You can book tickets there right now!";

        return $response;
    }
}
