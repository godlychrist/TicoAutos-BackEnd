<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Obtiene todos los mensajes enviados por el usuario actual
    public function index(Request $request)
    {
        $user = $request->user();
        $messages = Message::where('sender_id', $user->id)->get();
        return response()->json($messages);
    }

    // Guarda un nuevo mensaje en una conversación existente
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $userId = (string) ($user->_id ?? $user->id);

            // Evitar envíos consecutivos por la misma persona
            $lastMessage = Message::where('conversation_id', (string) $request->conversation_id)
                ->orderBy('_id', 'desc')
                ->first();

            if ($lastMessage && (string) $lastMessage->sender_id === $userId) {
                return response()->json([
                    'message' => 'Espera a que la otra persona responda!'
                ], 403);
            }

            // Crear el mensaje
            $message = Message::create([
                'conversation_id' => (string) $request->conversation_id,
                'sender_id' => $userId,
                'message' => $request->message,
            ]);

            // Actualizar el estado de la conversación general
            $conversation = Conversation::find($request->conversation_id);

            if (!$conversation) {
                return response()->json([
                    'message' => 'Conversación no encontrada'
                ], 404);
            }

            $conversation->update([
                'last_message' => $request->message,
                'last_message_at' => now()
            ]);

            return response()->json($message, 201);

        } catch (\Throwable $e) {
            \Log::error('STORE MESSAGE ERROR', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Obtiene todas las conversaciones (inbox) donde participa el usuario
    public function getConversations(Request $request) {
        $user = $request->user();
        $conversations = Conversation::where('buyer_id', $user->id)->orWhere('seller_id', $user->id)->get();
        return response()->json($conversations);
    }

    // Inicia un nuevo hilo de chat sobre un vehículo listado
    public function createConversation(Request $request) {
        $user = $request->user();
        if((string)$user->id === (string)$request->seller_id) {
            return response()->json(['message' => 'No puedes crear una conversación contigo mismo']);
        }

        // Retorna la conversación existente si ya había contacto previo
        $existing = Conversation::where('buyer_id', $user->id)->where('seller_id', $request->seller_id)->where('vehicle_id', $request->vehicle_id)->first();
        if($existing) {
            return response()->json($existing);
        }

        // Crea nueva conversación
        $conversation = Conversation::create([
            'buyer_id' => $user->id,
            'seller_id' => $request->seller_id,
            'vehicle_id' => $request->vehicle_id,
        ]);
        return response()->json($conversation);
    }

    // Obtiene todo el historial de mensajes de una conversación específica
    public function getConversation(Request $request, $id) {
        $conversation = Conversation::find($id);
        if(!$conversation) {
            return response()->json(['message' => 'No existe conversacion.'], 404);
        }

        // Válida permisos de participante
        $user = $request->user();
        if((string)$user->id !== (string)$conversation->seller_id && (string)$user->id !== (string)$conversation->buyer_id) {
            return response()->json(['message' => 'No tienes permiso para esta conversacion!'], 403);
        }
        
        $messages = Message::where('conversation_id', $id)->get();
        return response()->json($messages);
    }
}