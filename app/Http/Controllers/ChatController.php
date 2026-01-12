<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\Route;

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
            return "Dari {$r->source} ke {$r->destination->city_name} ($r->distance km)";
        })->implode('; ');

        $systemPrompt = "Anda adalah asisten perjalanan virtual yang ramah untuk 'Depart App', sebuah platform tiket bus di Indonesia.
        
        Berikut adalah konteks layanan kami:
        - Kami menawarkan tiket bus antar kota dan antar provinsi.
        - Metode pembayaran: Transfer Bank, E-Wallet (OVO, Dana, GoPay), Minimarket.
        - Refund: Tersedia 24 jam sebelum keberangkatan dengan biaya administrasi 25%.
        - Dukungan: 24/7 di 0800-1234-5678 atau support@departapp.com.

        Destinasi yang tersedia di database saat ini: {$destinations}.
        Contoh Rute: {$routes}.
        
        Tujuan Anda: Jawab pertanyaan pengguna dengan ringkas dalam Bahasa Indonesia. Jika mereka meminta rekomendasi, sarankan destinasi dari daftar kami dan jelaskan mengapa tempat itu menarik (buat deskripsi yang menyenangkan berdasarkan pengetahuan umum tentang kota tersebut). Jika mereka bertanya tentang pemesanan, pandu mereka untuk menggunakan bilah pencarian di beranda. Gunakan nada profesional namun hangat. Format: Gunakan tag HTML seperti <b>, <strong>, <br> untuk format.";

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nUser: " . $userMessage . "\nAssistant:"]
                    ]
                ]
            ]
        ]);

        $data = $response->json();

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'];
            $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
            $text = nl2br($text);
            return $text;
        }

        throw new \Exception('Invalid API response');
    }

    private function generateRuleBasedResponse($message)
    {
        // Greetings
        if (preg_match('/\b(hi|hello|hey|halo|hai|selamat pagi|selamat siang|selamat sore|selamat malam)\b/', $message)) {
            return "Halo! Selamat datang di " . config('app.name') . ". Saya dapat membantu Anda dengan info tiket, refund, atau rekomendasi wisata. Ada yang bisa saya bantu?";
        }

        // FAQs
        if (str_contains($message, 'book') || str_contains($message, 'ticket') || str_contains($message, 'reservation') || str_contains($message, 'pesan') || str_contains($message, 'tiket') || str_contains($message, 'beli')) {
            return "Untuk memesan tiket, silakan gunakan pencarian di beranda. Masukkan asal, tujuan, dan tanggal perjalanan, lalu klik 'Cari Bus'.";
        }

        if (str_contains($message, 'refund') || str_contains($message, 'cancel') || str_contains($message, 'batal') || str_contains($message, 'kembali')) {
            return "Refund tersedia jika pembatalan dilakukan minimal 24 jam sebelum keberangkatan. Biaya administrasi sebesar 25% berlaku.";
        }

        if (str_contains($message, 'pay') || str_contains($message, 'method') || str_contains($message, 'bayar')) {
            return "Kami menerima Transfer Bank, E-Wallet (OVO, Dana, GoPay), dan pembayaran di minimarket seperti Indomaret/Alfamart.";
        }

        if (str_contains($message, 'contact') || str_contains($message, 'support') || str_contains($message, 'help') || str_contains($message, 'kontak') || str_contains($message, 'bantuan') || str_contains($message, 'cs')) {
            return "Dukungan pelanggan kami tersedia 24/7. Hubungi kami di 0800-1234-5678 atau email support@departapp.com.";
        }

        // Tour Recommendations
        if (str_contains($message, 'recommend') || str_contains($message, 'tour') || str_contains($message, 'visit') || str_contains($message, 'suggest') || str_contains($message, 'trip') || str_contains($message, 'saran') || str_contains($message, 'wisata') || str_contains($message, 'jalan')) {
            return $this->getRecommendations();
        }

        // Default
        return "Maaf, saya kurang mengerti. Anda bisa bertanya tentang cara pesan tiket, kebijakan refund, metode pembayaran, atau minta rekomendasi wisata!";
    }

    private function getRecommendations()
    {
        // Get 3 random destinations that have routes
        $destinations = Destination::whereHas('routes')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        if ($destinations->isEmpty()) {
            return "Kami melayani banyak kota indah di seluruh negeri! Cek rute populer kami di beranda.";
        }

        $response = "Berikut beberapa destinasi menarik yang mungkin Anda suka:<br><br>";
        
        foreach ($destinations as $dest) {
            $response .= "<strong>" . $dest->city_name . "</strong>: Tempat yang luar biasa untuk dikunjungi! <br>";
        }

        $response .= "<br>Anda bisa pesan tiket ke sana sekarang juga!";

        return $response;
    }
}
