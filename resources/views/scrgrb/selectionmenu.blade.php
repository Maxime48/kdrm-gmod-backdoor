@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ScreenGrabber Selection Menu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        @if (session('status') !== null)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="alert alert-warning" role="alert">
                    {{ session('status') }}
                    @foreach($errors->all() as $error)
                        <div>
                            {{$error}}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    <div class="container-fluid mt-100">
                        <div class="card-group mx-8">

                            <div class="card rounded">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        Fast Screen Grab 🏎️
                                    </h5>
                                    <p class="card-text">
                                        Takes a screenshot of the player's screen using the selected name, uses the first player detected in case of duplicates. <br>
                                    </p>
                                </div>
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item-success"><i class="fa fa-clock-o"style="font-size:20px;"></i>   20-40 seconds</li>
                                </ul>
                                <div class="card-body mx-auto">
                                    <a href="{{ route('selectFast', ['serverid' => $server->id]) }}"  class="btn btn-primary">
                                        ☢ ️Launch ☢
                                    </a>
                                </div>
                            </div>
                            <div class="card rounded">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        Precise Screen Grab🎯
                                    </h5>
                                    <i class="fa-solid fa-arrows-to-dot"></i>
                                    <p class="card-text">
                                        Takes a screenshot of the player's screen using the selected steamid. <br><br>
                                    </p>
                                </div>
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item-success"><i class="fa fa-clock-o"style="font-size:20px;"></i>   40-60 seconds</li>
                                </ul>
                                <div class="card-body mx-auto">
                                    <a href="{{ route('selectPrecise', ['serverid' => $server->id]) }}"  class="btn btn-primary">
                                        ☢ ️Launch ☢
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
