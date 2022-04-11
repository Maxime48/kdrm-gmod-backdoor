@php
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Profile') }}
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
            <div class="container d-flex justify-content-center">
                <div class="container">
                    <div class="main-body">
                        <div class="row gutters-sm">
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex flex-column align-items-center text-center">
                                            <img src="@if($user->avatar !== 'nope') {{$user->avatar}} @else https://img.icons8.com/color/36/000000/administrator-male.png @endif" alt="Admin" class="rounded-circle" width="150">
                                            <div class="mt-3">
                                                <h4>{{$user->name}}</h4>
                                                <p class="text-muted font-size-sm">
                                                    @if($user->admin == 1)
                                                        Moderator
                                                    @elseif($user->admin == 2)
                                                        Admin
                                                    @elseif($user->admin == -1)
                                                        Banned
                                                    @else
                                                        User
                                                    @endif
                                                </p>
                                                <br>
                                                @if(Auth::user()->admin !== 0)
                                                    <button
                                                        class="btn btn-warning"
                                                        onclick="window.location='{{ route("user", ['id' => $user->id]) }}'" >
                                                             Edition
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        @if($user->banner !== 'nope')
                                            <img style="box-sizing: border-box; max-height: 20rem;" src="{{ $user->banner }}" class="rounded mx-auto d-block" alt="banner">
                                        @endif

                                        @if($user->bio !== 'nope')
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-12 align-items-center text-center">
                                                    <h6 class="mb-0">{{$user->bio}}</h6>
                                                </div>
                                            </div>
                                        @endif
                                        <hr>
                                            <div class="row justify-content-center">

                                                <div class="col-md-3">
                                                    <div class="card-counter info">
                                                        <i class="fa fa-server"></i>
                                                        <span class="count-numbers"> {{ $server_count }} </span>
                                                        <span class="count-name">Server</span>
                                                    </div>
                                                </div>

                                            </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
