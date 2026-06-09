<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    public function sendNewOrderNotification(Sale $sale)
    {
        if (!$this->botToken || !$this->chatId) {
            Log::warning('Telegram bot token or chat ID is missing. Cannot send notification.');
            return false;
        }

        $message = $this->formatOrderMessage($sale);

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Service exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function formatOrderMessage(Sale $sale): string
    {
        $text = "🛒 <b>NEW ONLINE ORDER</b>\n\n";
        // $text .= "<b>Invoice:</b> {$sale->invoice_number}\n";
        $text .= "<b>Time:</b> " . now()->format('d M Y, h:i A') . "\n";
        // $text .= "<b>Customer Type:</b> " . ucfirst($sale->customer_type) . "\n";
        $text .= "<b>Customer:</b> {$sale->contact_name}\n";
        $text .= "<b>Phone:</b> {$sale->contact_number}\n";
        $text .= "<b>Address:</b> {$sale->address}\n";
        // $text .= "<b>COD:</b> " . ($sale->cod ? 'Yes' : 'No') . "\n";
        // $text .= "<b>Note:</b> " . ($sale->note ?: 'None') . "\n";
        
        
        $text .= "<b>Items:</b>\n";
        foreach ($sale->saleItems as $item) {
            $productName = $item->product ? $item->product->name : 'Unknown Product';
            $text .= "- {$item->quantity}x {$productName} ($" . number_format($item->line_total, 2) . ")\n";
        }

        if ($sale->delivery_fee > 0) {
            $text .= "\n🚚 <b>Delivery Fee: $" . number_format($sale->delivery_fee, 2) . "</b>";
        }
        if ($sale->discount > 0) {
            $text .= "\n📉 <b>Discount: $" . number_format($sale->discount, 2) . "</b>";
        }

        $text .= "\n💰 <b>Total: $" . number_format($sale->total, 2) . "</b>\n";
        $text .= "\n<i>Please review and approve in the admin panel.</i>";

        return $text;
    }
}
