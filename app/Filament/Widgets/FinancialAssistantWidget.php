<?php

namespace App\Filament\Widgets;

use App\AI\Agents\FinancialAssistant;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class FinancialAssistantWidget extends Widget
{
    protected static string $view = 'filament.widgets.financial-assistant';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1; // Mostrar al principio del dashboard

    public string $message = '';

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $isLoading = false;

    public function mount(): void
    {
        // Inicializar con mensaje de bienvenida
        $this->messages = [];
    }

    #[On('send-message')]
    public function sendMessage(): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        $this->isLoading = true;

        // Agregar mensaje del usuario
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->message,
            'created_at' => now()->toISOString(),
        ];

        $userMessage = $this->message;
        $this->message = '';

        try {
            $agent = new FinancialAssistant;

            if ($this->conversationId) {
                // Continuar conversación existente
                $response = $agent->continue($this->conversationId, as: Auth::user())
                    ->prompt($userMessage);
            } else {
                // Nueva conversación
                $response = $agent->forUser(Auth::user())
                    ->prompt($userMessage);
                $this->conversationId = $response->conversationId;
            }

            // Agregar respuesta del asistente
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $response->text,
                'created_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => '❌ Lo siento, hubo un error al procesar tu mensaje: '.$e->getMessage(),
                'created_at' => now()->toISOString(),
            ];
        }

        $this->isLoading = false;
    }

    public function clearConversation(): void
    {
        $this->messages = [];
        $this->conversationId = null;
        $this->message = '';
    }

    public function getListeners(): array
    {
        return [
            'send-message' => 'sendMessage',
        ];
    }
}
