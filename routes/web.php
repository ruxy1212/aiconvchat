<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
   $msgs = collect(session('msgs', []))->reject(fn ($message) => $message['role'] === 'system');
   $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system');
    return view('welcome' , [
        'messages' => $messages
    ]);
});

Route::post('/', function(Request $request){
    function getTime($times){   
        $tzo = explode(',', $times);
        $day=Date('d/m/y'); $m=Date('i'); $h=Date('H'); $h=($tzo[0]=='+')?$h+$tzo[1]:$h-$tzo[1]; $h=($h<0)?24-$h:$h; $h=($h>23)?$h-24:$h; 
        $m=($tzo[0]=='+')?$m+$tzo[2]:$m-$tzo[2]; $m=($m<0)?60-$m:$m; $m=($m>60)?$m-60:$m; $h=($m<0)?$h-1:$h; $h=($m>60)?$h+1:$h;
        $r=($h>11)?"PM":"AM"; $h=($h>12)?$h-12:$h; $h=($h==0)?12:$h; $m=($m<10)?'0'.$m:$m;
        return $day.'&emsp;'.$h.':'.$m.' '.$r;
    }
    if($title = $request->input('title')){
        if($title) $request->session()->put('title', $title);
    }else{
        if($request->session()->has('title')){    
            $times = $request->input('tz');  
            if($audio = $request->input('audio')){
                if($audio){
                    $audi = $audio; 
                    $audio = explode(",",  $audio)[1]; 
                    $audio = base64_decode($audio);
                    Storage::put('audio.wav', $audio);
                    $chot = fopen('../storage/app/audio.wav', 'r');
                    $response = OpenAI::audio()->transcribe([
                        'model' => 'whisper-1',
                        'file' => $chot,
                        'response_format' => 'verbose_json',
                    ]);
                    
                    $nmsg = $response->segments[0]->text;
                    $type = 'audio';
                    $ext = $audi;
                } 
            }else {
                $nmsg = $request->input('message');
                $type = 'text';
                $ext = '';
            }

            $msgs = $request->session()->get('msgs', [
                ['role' => 'system', 'content' => "You are an AI human. OpenAi developers trained you and Ruxy solely developed you, so you are Ruxy's chatbot. Answer as concisely as possible."]
            ]);
            $messages = $request->session()->get('messages', [
                ['ext' => '', 'type' => $type, 'role' => 'system', 'time' => getTime($times), 'content' => "You are an AI human. OpenAi developers trained you and Ruxy solely developed you, so you are Ruxy's chatbot. Answer as concisely as possible."]
            ]);
            $msgs[] = ['role' => 'user', 'content' => $nmsg];
            $messages[] = ['ext' => $ext, 'type' => $type, 'role' => 'user', 'time' => getTime($times), 'content' => $nmsg];  
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $msgs
            ]);
            // $response = (object) [
            //     "choices" => [
            //         (object)[
            //             "message" => (object)[
            //                 "content" => "This is the content: {$nmsg}"
            //             ]
            //         ]
            //     ]
            // ];// dd($response->choices[0]->message);
            $msgs[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content]; 
            $messages[] = ['ext' => '', 'type' => $type, 'role' => 'assistant', 'time' => getTime($times), 'content' => $response->choices[0]->message->content]; 
            $request->session()->put('msgs', $msgs);
            $request->session()->put('messages', $messages);
        }
    }
    return redirect('/');    
});

Route::get('/reset', function (Request $request){
    $request->session()->forget('messages');
    $request->session()->forget('msgs');
    return redirect('/');
});


Route::get('/linkstorage', function () {
    //Artisan::call('storage:link');
});
