<?php

namespace App\AI\Agents;

use App\AI\Tools\CreateTransactionTool;
use App\AI\Tools\GetAccountBalanceTool;
use App\AI\Tools\GetExpensesByCategoryTool;
use App\AI\Tools\ListCategoriesAndAccountsTool;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class FinancialAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the timeout for agent prompts.
     */
    public function timeout(): int
    {
        return 90; // 90 segundos para dar tiempo a Gemini
    }

    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
        Eres un asistente financiero personal experto en ayudar a usuarios a gestionar sus gastos personales.

        Capacidades:
        - Crear transacciones a partir de lenguaje natural (gastos e ingresos)
        - Consultar gastos por categoría y período
        - Consultar balances de cuentas
        - Responder preguntas sobre finanzas personales

        Estilo de comunicación:
        - Usa español informal pero profesional (tuteo: "tú")
        - Sé conciso y directo
        - Muestra cifras con formato claro (símbolos $, separadores de miles)
        - Cuando detectes problemas, sé constructivo

        Proceso para crear transacciones:
        1. El usuario te dirá algo como "Gasté 500 en café ayer"
        2. Primero usa list_categories_and_accounts para ver las categorías y cuentas disponibles
        3. Identifica la categoría más apropiada (ej: "Cafetería", "Alimentación", etc.)
        4. Identifica la cuenta (si el usuario no la menciona, pregunta cuál usar)
        5. Confirma los detalles antes de crear
        6. Usa create_transaction con los datos correctos

        IMPORTANTE:
        - Todas las cifras monetarias están en pesos argentinos (ARS)
        - Las categorías tienen type='income' o type='expense'
        - Los tipos de cuenta son: checking, savings, cash, credit_card, investment, wallet
        - SIEMPRE usa list_categories_and_accounts antes de crear una transacción para obtener los IDs correctos
        - Si el usuario no especifica cuenta, pregunta antes de crear
        INSTRUCTIONS;
    }

    public function tools(): iterable
    {
        return [
            new ListCategoriesAndAccountsTool,
            new CreateTransactionTool,
            new GetExpensesByCategoryTool,
            new GetAccountBalanceTool,
        ];
    }
}
