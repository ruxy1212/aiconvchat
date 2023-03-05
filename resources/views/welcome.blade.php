<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>RAI Convo</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <link href="{{ asset('rlogo.png') }}" rel="icon" />
    </head>
    <body class="antialiased min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900">
        <header style="position: sticky; top: 0; z-index: 20;">
            <div class="focal-outline scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex sm:justify-betweens" style="">
                <a href="javascript:void(0);" onclick="freshConv()" class="hbtn">Refresh</a>
                <div class="flex justify-center">
                    <img class="h-16 w-auto" src="{{ asset('rlogo.png') }}" alt="Logo" style="visibility: hidden; margin-bottom: -25px" />
                </div>
                <a href="javascript:void(0);" onclick="openExt()" class="hbtn">Extras</a>
            </div>
        </header>
        
        <div class="unsee bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent" style="height: 100%; width: 100%; position: fixed; z-index: 9;">
            <div class="relative" style="height: 100%; width: 100%; width: 500px; max-width: 80%; display: block; margin: 0 auto;">
                <div class="flex justify-center items-center" style="height: 100%;">
                    <div class="focal-outline scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none focusw:outline focusw:outline-2 focusw:outline-red-500" style="width: 100%;">
                        <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">Config</h2>
                        <div class="flex gap-4 mt-4">
                            <div  style="flex-direction: column; display: flex; max-width: 75%;">
                                <label class="text-gray-900 dark:text-bisque text-sm leading-relaxed" style="padding: 0 10px;" for="title"><strong>Enter key to Send</strong></label>
                                <small class="text-gray-500 dark:text-gray-400" style="padding: 0 10px; line-height: 1;">Enter will send your message (Shift+Enter to return to a new line)</small>
                            </div> 
                            <div class="config-div">
                                <label class="config-switch" for="config">
                                    <input type="checkbox" id="config" />
                                    <div class="slider round"></div>
                                </label>
                            </div>
                        </div>
                        <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">About</h2>
                        <p class="mt-4 text-gray-900 dark:text-bisque text-sm leading-relaxed">
                            This App, RAI Chat is a conversational AI chatbot application using the latest GPT3.5 Turbo AI Model made available by OpenAI on March 1st, 2023. Enjoy, but sha don't expend all my tokens. 1 token is equivalent to 0.75 words!
                        </p>
                        <i><p class="text-gray-900 dark:text-bisque text-sm leading-relaxed">This application is being developed and maintained on this <a style="text-decoration: underline;" href="https://github.com/ruxy1212/aiconvchat" target="_blank">Github repo</a>. Kindly give me a star on <a style="text-decoration: underline;" href="https://github.com/ruxy1212" target="_blank">Github</a> or <a href="https://www.buymeacoffee.com/ruxy1212" target="_blank">Buy me a coke</a>.</p></i>
                        <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">Credits</h2>
                        <ul style="list-style: inside;" class="mt-4 text-gray-900 dark:text-bisque text-sm leading-relaxed">
                            <li><a href="https://github.com/openai-php/laravel">OpenAI Laravel</a></li>
                            <li><a href="https://github.com/ruxy1212">Ruxy1212</a></li>
                        </ul>
                    </div>
                </div>
            </div>         
        </div>

        <div class="@if(session()->has('title')) unsee @else see @endif bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent" style="height: 100%; width: 100%; position: fixed; z-index: 9;">
            <div class="relative" style="height: 100%; width: 100%; width: 500px; max-width: 80%; display: block; margin: 0 auto;">
                <div class="focal-outline scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex focusw:outline focusw:outline-2 focusw:outline-red-500" style="position: absolute; bottom: 200px; width: 100%;">
                    <form class="wwide" action="" method="post" style="padding: 10px;">
                    {{-- {{route('generate.qr')}} --}}
                        @csrf
                        <label class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed" style="display: block; text-align: center; padding: 10px;" for="title"><strong>Your Name</strong></label>
                            <div class="input-group">
                                <input type="text" style="width: 100%; outline: none; padding: 10px;" required name="title" placeholder="Enter your name"/>
                            </div>
                            <br>
                        <input type="submit" class="focal-outline font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" style="cursor: pointer; display: block; margin: 0 auto; padding: 10px;" value="Proceed" name="tsubmit" />
                    </form>
                </div>
            </div>    
        </div>

        <div class="relative selection:bg-red-500 selection:text-white">
            <div class="max-w-7xl mx-auto p-6 lg:p-8" style="padding-bottom: 0">
                <div class="flex justify-center">
                    <img class="h-16 w-auto" src="{{ asset('rlogo.png') }}" alt="Logo" />
                </div>

                @foreach($messages as $message)
                        <div class="boxc">
                            <div class="flex" @if($message['role'] === 'user') style="flex-direction: row-reverse; margin: 0 0 0 auto;" @endif>
                                <div>
                                    <div class="@if($message['role'] === 'user') relative @endif h-16 w-16 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                      @if($message['role'] == 'assistant')
                                        <img class="h-166 w-auto" src="{{ asset('rlogo.png') }}" alt="Logo" />
                                      @else 
                                        <img class="h-166 w-auto" src="{{ asset('clogo.png') }}" alt="Logo" />
                                        <span class="w-166">@if(session()->has('title')) {{ strtoupper(Session::get('title')[0]) }} @else R @endif  </span>
                                      @endif
                                    </div>
                                </div>
                                <a href="javascript:void(0);" class="scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500">
                                    <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                        {!! $message['content'] !!}
                                    </p>
                                </a>
                            </div>
                        </div>
            @endforeach





            </div>
        </div>
                <div class="boxr">
                    <div class="p-6">
                        <div class="scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex focusw:outline focusw:outline-2 focusw:outline-red-500 inpxb">
                            <form class="relative sm:flex sm:justify-center sm:items-center wwide" action="" method="post">
                                @csrf
                                <textarea type="text" rows="1" onkeyup="txAdjust(this)" class="bdls-input wwide text-gray-500 dark:text-gray-400 text-sm" required name="message" autofocus autocomplete="off" placeholder="Write..."></textarea>
                                <button class="fbtn">
                                    <div class="h-8 w-8 bg-red-50 flex items-center justify-center rounded-full">
                                    {{-- dark:bg-red-800/20  --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="self-center shrink-0 stroke-red-500 w-6 h-6 mx-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                                        </svg>
                                    </div>
                                </button>
                            </form>
        
                        
                            
                        </div>
                    </div>
                </div>
        <script src="{{ asset('js/script.js') }}"></script>
    </body>
</html>
