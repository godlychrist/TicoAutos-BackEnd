<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

/**
 * MessageController - Sistema de mensajería entre compradores y vendedores.
 *
 * Permite crear conversaciones vinculadas a un vehículo, enviar mensajes
 * dentro de esas conversaciones, y consultar el historial de chat.
 * Todas las rutas de este controlador requieren autenticación JWT.
 */
class MessageController extends Controller
{
    /** Obtener todos los mensajes enviados por el usuario autenticado */
    public function index(Request $request)
    {
        $user = $request->user();
        $messages = Message::where('sender_id', $user->id)->get();
        return response()->json($messages);
    }

    /**
     * Enviar un mensaje dentro de una conversación existente.
     *
     * Incluye protección anti-spam: no permite enviar dos mensajes
     * consecutivos del mismo usuario (debe esperar respuesta).
     * Actualiza el campo 'last_message' y timestamps de la conversación.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $userId = (string) ($user->_id ?? $user->id);
            $createdAt = now();
            $updateData = [];

            // Verificar si el último mensaje fue del mismo usuario (anti-spam)
            $lastMessage = Message::where('conversation_id', (string) $request->conversation_id)
                ->orderBy('_id', 'desc')
                ->first();

            if ($lastMessage && (string) $lastMessage->sender_id === $userId) {
                return response()->json([
                    'message' => 'Espera a que la otra persona responda!'
                ], 403);
            }

            $message = Message::create([
                'conversation_id' => (string) $request->conversation_id,
                'sender_id' => $userId,
                'message' => $request->message,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]);

            $conversation = Conversation::find($request->conversation_id);

            if (!$conversation) {
                return response()->json([
                    'message' => 'Conversación no encontrada'
                ], 404);
            }

            // Registrar timestamp del último mensaje por rol (buyer/seller)
            if((string)$userId == (string)$conversation->buyer_id ) {
               $updateData['buyer_msg'] = now();
            }

            if((string)$userId == (string)$conversation->seller_id ) {
               $updateData['seller_msg'] = now();
            }
            $updateData['last_message'] = $request->message;
            $updateData['last_message_at'] = now();

            $conversation->update($updateData);

            return response()->json($message, 201);

        } catch (\Throwable $e) {
            \Log::error('STORE MESSAGE ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener todas las conversaciones del usuario autenticado.
     * Incluye datos del comprador, vendedor y vehículo (eager loading).
     */
    public function getConversations(Request $request) {
        $user = $request->user();
        $userId = (string) ($user->_id ?? $user->id);
        $conversations = Conversation::with(['buyer', 'seller', 'vehicle'])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->get();
        return response()->json($conversations);
    }

    /**
     * Crear una nueva conversación entre comprador y vendedor.
     *
     * Validaciones: no permite crear una conversación consigo mismo,
     * y reutiliza una conversación existente si ya existe para el mismo
     * par de usuarios y vehículo (evita duplicados).
     */
    public function createConversation(Request $request) {
        $user = $request->user();
        if((string)$user->id === (string)$request->seller_id) {
            return response()->json(['message' => 'No puedes crear una conversación contigo mismo']);
        }

        $existing = Conversation::where('buyer_id', $user->id)->where('seller_id', $request->seller_id)->where('vehicle_id', $request->vehicle_id)->first();
        if($existing) {
            return response()->json($existing);
        }
        $conversation = Conversation::create([
            'buyer_id' => $user->id,
            'seller_id' => $request->seller_id,
            'vehicle_id' => $request->vehicle_id,
        ]);
        return response()->json($conversation);
    }

    /**
     * Obtener los mensajes de una conversación específica.
     *
     * Verifica que el usuario autenticado sea participante de la conversación.
     * Formatea los timestamps a hora de Costa Rica (America/Costa_Rica)
     * en formato legible (hh:mm AM/PM).
     */
    public function getConversation(Request $request, $id) {
        $conversation = Conversation::find($id);
        if(!$conversation) {
            return response()->json(['message' => 'No existe conversacion.'], 404);
        }

        $user = $request->user();
        if((string)$user->id !== (string)$conversation->seller_id && (string)$user->id !== (string)$conversation->buyer_id) {
            return response()->json(['message' => 'No tienes permiso para esta conversacion!'], 403);
        }
        $messages = Message::where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($msg) {
                $time = '';
                if ($msg->created_at) {
                    try {
                        $time = \Carbon\Carbon::parse($msg->created_at)
                            ->setTimezone('America/Costa_Rica')
                            ->format('h:i A');
                    } catch (\Exception $e) {}
                }
                return [
                    '_id'             => (string)$msg->_id,
                    'conversation_id' => (string)$msg->conversation_id,
                    'sender_id'       => (string)$msg->sender_id,
                    'message'         => $msg->message,
                    'time'            => $time,
                ];
            });

        return response()->json($messages);
    }
}
