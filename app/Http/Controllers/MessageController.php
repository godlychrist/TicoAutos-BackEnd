<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $messages = Message::where('sender_id', $user->id)->get();
        return response()->json($messages);
    }

    public function store(Request $request) {
        $user = $request->user();
        
        //Revisa el turno
        $lastMessage = Message::where('conversation_id', $request->conversation_id)
        ->orderBy('created_at', 'desc')
        ->first();
        if($lastMessage && $lastMessage->sender_id == $user->id) {
            return response()->json(['message' => 'Espera a que la otra persona responda!'], 403);
        }

        // Crea el mensaje
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        // Actualiza
        $conversation = Conversation::find($request->conversation_id);
        $conversation->update([
            'last_message' => $request->message,
            'last_message_at' => now()
        ]);


        return response()->json($message);


    }

    public function getConversations(Request $request) {
        $user = $request->user();
        $conversations = Conversation::where('buyer_id', $user->id)->orWhere('seller_id', $user->id)->get();
        return response()->json($conversations);
    }

    // 
    public function createConversation(Request $request) {
        $user = $request->user();
        if($user->id == $request->seller_id) {
            return response()->json(['message' => 'No puedes crear una conversación contigo mismo']);
        }

        // Verificar si ya existe una conversación entre el comprador y el vendedor
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

    // Conseguir los mensajes de una conversacion en especifico
    public function getConversation(Request $request, $id) {
        $conversation = Conversation::find($id);
        if(!$conversation) {
            return response()->json(['message' => 'No existe conversacion.'], 404);
        }

        $user = $request->user();
        if($user->id != $conversation->seller_id && $user->id != $conversation->buyer_id) {
            return response()->json(['message' => 'No tienes permiso para esta conversacion!'], 403);
        }
        $messages = Message::where('conversation_id', $id)->get();
        return response()->json($messages);
    }
}