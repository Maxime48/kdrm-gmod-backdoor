<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User') }}
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
                                            <img src="@if($user->avatar !== 'nope') {{$user->avatar}} @else https://img.icons8.com/color/36/000000/administrator-male.png @endif" alt="Admin" class="rounded-circle" style="width: 15rem; height: 15rem;">
                                            <div class="mt-3">
                                                <h4>{{$user->name}}</h4>
                                                <p class="text-secondary mb-1">{{$user->bio}}</p>
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

                                                    <button
                                                        class="btn btn-warning"
                                                        onclick="window.location='{{ route("promotedown", ['id' => $user->id]) }}'" >
                                                             Demote
                                                    </button>

                                                    <button
                                                        class="btn btn-success"
                                                        onclick="window.location='{{ route("unban", ['id' => $user->id]) }}'">
                                                            Unban
                                                    </button>

                                                    <button
                                                        class="btn btn-success"
                                                        onclick="window.location='{{ route("promote", ['id' => $user->id]) }}'">
                                                            Promote
                                                    </button>

                                                    <button
                                                        class="btn btn-danger"
                                                        onclick="window.location='{{ route("ban", ['id' => $user->id]) }}'">
                                                                Ban
                                                    </button>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        @if($mode)
                                        <form method="POST" action="{{ route('adminModify', ['id' => $user->id]) }}">
                                            @csrf
                                            <div>
                                                <x-label for="name" :value="__('Name')" />

                                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{$user->name}}" required autofocus />
                                            </div>
                                            <div>
                                                <x-label for="forename" :value="__('Forename')" />

                                                <x-input id="forename" class="block mt-1 w-full" type="text" name="forename" value="{{$user->forename}}" autofocus />
                                            </div>
                                            <div>
                                                <x-label for="surname" :value="__('Surname')" />

                                                <x-input id="surname" class="block mt-1 w-full" type="text" name="surname" value="{{$user->surname}}" autofocus />
                                            </div>
                                            <div>
                                                <x-label for="email" :value="__('Email')" />

                                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{$user->email}}" required autofocus />
                                            </div>

                                            @if($user->bio == 'nope')
                                                <div>
                                                    <x-label for="bio" :value="__('Bio')" />

                                                    <x-input id="bio" class="block mt-1 w-full" type="text" name="bio" autofocus />
                                                </div>
                                                @else
                                            <div>
                                                <x-label for="bio" :value="__('Bio')" />

                                                <x-input id="bio" class="block mt-1 w-full" type="text" name="bio" value="{{$user->bio}}" autofocus />
                                            </div>
                                            @endif
                                            @if($user->banner == 'nope')
                                                <div>
                                                    <x-label for="banner" :value="__('Banner url')" />

                                                    <x-input id="banner" class="block mt-1 w-full" type="text" name="banner" autofocus />
                                                </div>
                                                @else
                                            <div>
                                                <x-label for="banner" :value="__('Banner url')" />

                                                <x-input id="banner" class="block mt-1 w-full" type="text" name="banner" value="{{$user->banner}}" autofocus />
                                            </div>
                                            @endif
                                            @if($user->avatar == 'nope')
                                                <div>
                                                    <x-label for="avatar" :value="__('Avatar Url')" />

                                                    <x-input id="avatar" class="block mt-1 w-full" type="text" name="avatar" autofocus />
                                                </div>
                                                @else
                                            <div>
                                                <x-label for="avatar" :value="__('Avatar Url')" />

                                                <x-input id="avatar" class="block mt-1 w-full" type="text" name="avatar" value="{{$user->avatar}}" autofocus />
                                            </div>
                                            @endif

                                            <div id="adminusermodify"></div>

                                            <x-button class="mt-2">
                                                {{ __('Modify') }}
                                            </x-button>
                                        </form>
                                            {!!  GoogleReCaptchaV3::render(['adminusermodify'=>'adminusermodify']) !!}
                                        @else

                                        <div class="row">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Full Name</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                {{$user->forename." ".$user->surname}}
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Email</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                {{$user->email}}
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Joined</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                {{$user->created_at}}
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">Last Update</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                {{$user->updated_at}}
                                            </div>
                                        </div>
                                        <hr>
                                            <div class="row" style="text-align: center;">
                                                <div class="col-sm-12">
                                                    <a class="btn btn-info " target="__blank" href="{{ route('changeMode', ['id' => $user->id]) }}">
                                                        Edition mode
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-body">
                                        @if($servers != null)
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Player</th>
                                                        <th scope="col">Ip</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Last Update</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($servers as $server)
                                                        <tr>
                                                            <td>{{ $server->name }}</td>
                                                            <td>{{ $server->players }}</td>
                                                            <td>{{ $server->ip }}:{{ $server->port }}</td>
                                                            <td>{{ $server->status }}</td>
                                                            <td>{{ $server->updated_at }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
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
