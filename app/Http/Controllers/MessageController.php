<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Message;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $message = new Message();
        $message->text = $request->input('text');
        $message->sender_id = auth()->user()->id;
        // $message->sender_id = $request->input('sender_id');
        $message->receiver_id = $request->input('receiver_id');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('message_files', 'public'); // يتم تخزين الملف في مجلد public/storage/message_files
            $message->file = $path;
        }

        $message->save();

        return response()->json(['message' => 'تم إرسال الرسالة بنجاح']);
    }

    public function index()
    {
        $messages = Message::with('sender')->get(); // افترض أن لديك علاقة sender تشير إلى نموذج المستخدم (User)

        return response()->json($messages);
    }
}

