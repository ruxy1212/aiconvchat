<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>RAI Chat</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="{{ asset('css/style.css') }}" rel="stylesheet" />
        <link href="{{ asset('images/rlogo.png') }}" rel="icon" />
        <link href="{{ asset('css/highlight.css') }}"rel="stylesheet" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body class="antialiased min-h-screen bg-dots-darker bg-center bg-gray-200 dark:bg-dots-lighter dark:bg-gray-900">
        <header style="position: sticky; top: 0; z-index: 20;">
            <div class="focal-outliner scale-100 p-3 bg-gray-100 dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex sm:justify-betweens" style="">
                <a href="javascript:void(0);" onclick="freshConv()" class="hbtn">Refresh</a>
                <div class="flex justify-center">
                    <img class="h-16 w-auto" src="{{ asset('images/rlogo.png') }}" alt="Logo" style="visibility: hidden; margin-bottom: -25px" />
                </div>
                <a href="javascript:void(0);" onclick="openExt()" class="hbtn">Extras</a>
            </div>
        </header>
        
        <div class="unsee bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent bimodal">
            <div class="relative">
                <div class="flex justify-center items-center" style="height: 100%;">
                    <div class="focal-outline scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none focusw:outline focusw:outline-2 focusw:outline-red-500" style="width: 100%;">
                        <h2 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">Config</h2>
                        <div class="flex gap-4 mt-4 sm:justify-betweens">
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
                            This App, RAI Chat is a conversational AI chatbot application using the latest GPT3.5 Turbo and Whisper AI Models made available by OpenAI on March 1st, 2023. Whisper AI translates the voice recording, while GPT Turbo creates the conversational chat. Enjoy, but sha don't expend all my tokens. 1 token is equivalent to 0.75 words! Also, the voice note recording time limit is 15seconds.
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

        <div class=" @if(session()->has('title')) unsee @else see @endif bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent" style="height: 100%; width: 100%; position: fixed; z-index: 9;">
            <div class="relative" style="height: 100%; width: 100%; width: 500px; max-width: 80%; display: block; margin: 0 auto;">
                <div class="focal-outline scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex focusw:outline focusw:outline-2 focusw:outline-red-500" style="position: absolute; bottom: 200px; width: 100%;">
                    <form class="wwide" action="" method="post" style="padding: 10px;">
                        @csrf
                        <label class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed" style="display: block; text-align: center; padding: 10px;" for="title"><strong>Your Name</strong></label>
                            <div class="input-group">
                                <input class="bg-gray-100 dark:bg-gray-900 text-gray-500 dark:text-gray-400 text-sm" type="text" style="width: 100%; outline: none; padding: 10px;" required name="title" placeholder="Enter your name"/>
                            </div>
                            <br>
                        <input type="submit" class="focal-outline font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" style="cursor: pointer; display: block; margin: 0 auto; padding: 10px;" value="Proceed" name="tsubmit" />
                    </form>
                </div>
            </div>    
        </div>

        <div class="relative selection:bg-red-500 selection:text-white bdrop">
            <div class="max-w-7xl mx-auto p-6 lg:p-8" style="padding-bottom: 0">
                <div class="flex justify-center">
                    <img class="h-16 w-auto" src="{{ asset('images/rlogo.png') }}" alt="Logo" />
                </div>
                @foreach($messages as $message)
                    <div class="boxc">
                        <div class="flex" @if($message['role'] === 'user') style="flex-direction: row-reverse; margin: 0 0 0 auto;" @endif>
                            <div>
                                <div class="@if($message['role'] === 'user') relative @endif h-16 w-16 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                                    @if($message['role'] == 'assistant')
                                    <img class="h-166 w-auto" src="{{ asset('images/rlogo.png') }}" alt="Logo" />
                                    @else 
                                    <img class="h-166 w-auto" src="{{ asset('images/clogo.png') }}" alt="Logo" />
                                    <span class="w-166">@if(session()->has('title')) {{ strtoupper(Session::get('title')[0]) }} @else R @endif  </span>
                                    @endif
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500" style="flex-direction: column; margin-bottom: 10px;">
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed c-content">{!! $message['content'] !!}</p>
                                @if($message['role'] === 'user')
                                    @if($message['type'] == 'audio')
                                      <audio controls controlsList="nodownload"><source src="{!! $message['ext'] !!}" type="audio/ogg"></audio>
                                    @endif
                                @endif
                                <small style="position: absolute; bottom: -18px; @if($message['role'] === 'user') right @else left @endif : 0; white-space: nowrap;font-size: x-small;" class="text-gray-900 dark:text-bisque">{!! $message['time'] !!}</small>
                            </a>                           
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="boxr">
            <div class="p-6">
                <div class="scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex focusw:outline focusw:outline-2 focusw:outline-red-500 inpxb">
                    <form class="relative sm:flex sm:justify-center sm:items-center wwide formmaintain" action="" method="post">
                        @csrf
                        <textarea type="text" rows="1" onkeyup="txAdjust(this)" class="bdls-input wwide text-gray-500 dark:text-gray-400 text-sm hlong" name="message" autofocus autocomplete="off" placeholder="Write..." ></textarea>
                        <input type="hidden" name="tz" id="tzo"/>
                        <button class="fbtn" data="play">
                            <div class="h-8 w-8 bg-red-50 flex items-center justify-center rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="self-center shrink-0 stroke-red-500 w-6 mx-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1000 1000" class="self-center shrink-0 stroke-red-500">
                                    <path d="M395,325c0-57.8,47.3-105,105-105s105,47.2,105,105v175c0,57.8-47.3,105-105,105s-105-47.3-105-105V325z M675,500c0,90.3-69.4,165.3-157.5,174.1V745H605v35h-87.5h-35H395v-35h87.5v-70.9C394.4,665.3,325,590.3,325,500v-87.5h35v86.6c0,77,63,140,140,140c77,0,140-63,140-140v-86.6h35L675,500L675,500z"/>
                                </svg>
                            </div>
                        </button>
                        <span class="fbtnt rcd-cancel" style="visibility: hidden;" onclick="rcdCancel()">
                            <div class="h-8 w-8 bg-red-50 flex items-center justify-center rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1000 1000"  class="self-center shrink-0 stroke-red-500">
                                    <path d="M738.2,718.6l-19.6,19.6c-6.2,6.2-16.2,6.2-22.4,0L500,542L303.8,738.2c-6.2,6.2-16.2,6.2-22.4,0l-19.6-19.6c-6.2-6.2-6.2-16.2,0-22.4L458,500L261.8,303.8c-6.2-6.2-6.2-16.2,0-22.4l19.6-19.6c6.2-6.2,16.2-6.2,22.4,0L500,458l196.2-196.2c6.2-6.2,16.2-6.2,22.4,0l19.6,19.6c6.2,6.2,6.2,16.2,0,22.4L542,500l196.2,196.2C744.4,702.4,744.4,712.4,738.2,718.6z"/>
                                </svg>
                            </div>
                        </span>
                        <span class="fbtnt rcd-timer" style="visibility: hidden; left: 20px; right: unset;">
                            <div class="h-8 w-8 flex items-center justify-center rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1000 1000" class="rd-rcdng-dt self-center shrink-0 stroke-red-500">
                                    <path d="M395,325c0-57.8,47.3-105,105-105s105,47.2,105,105v175c0,57.8-47.3,105-105,105s-105-47.3-105-105V325z M675,500c0,90.3-69.4,165.3-157.5,174.1V745H605v35h-87.5h-35H395v-35h87.5v-70.9C394.4,665.3,325,590.3,325,500v-87.5h35v86.6c0,77,63,140,140,140c77,0,140-63,140-140v-86.6h35L675,500L675,500z"/>
                                </svg>
                                <label class="text-gray-500 dark:text-gray-400">0:00</label>
                            </div>
                        </span>
                    </form>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="{{ asset('js/segment.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/highlight.js') }}"></script>
        <script>hljs.highlightAll();</script>
        <script type="text/javascript" src="{{ asset('src/AudioRecord.js') }}"></script>
        <script type="text/javascript" src="{{ asset('src/AudioRecorder.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/record.js') }}"></script>
        <script src="{{ asset('js/script.js') }}"></script>
    </body>
</html>
