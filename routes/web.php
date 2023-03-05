<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

Route::get('/', function () {
   $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system');
    return view('welcome' , [
        'messages' => $messages
    ]);
});

Route::post('/', function(Request $request){
    if($title = $request->input('title')){
        if($title) $request->session()->put('title', $title);
    }else{
        if($request->session()->has('title')){  
            $messages = $request->session()->get('messages', [
                ['role' => 'system', 'content' => "You are an AI person. OpenAi developers trained you and he solely developed you, so you are Ruxy's chatbot. Answer as concisely as possible."]
            ]);
            $messages[] = ['role' => 'user', 'content' => $request->input('message')];
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ]);
            $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];
            $request->session()->put('messages', $messages);
        }
    }
    return redirect('/');
});

Route::get('/reset', function (Request $request){
    $request->session()->forget('messages');
    return redirect('/');
});


