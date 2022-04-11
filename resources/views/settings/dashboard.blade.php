<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Settings') }}
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
                                                    <br>
                                                        <button
                                                            class="btn btn-primary"
                                                            onclick="window.location='{{ route("showUserProfile", ['id' => $user->id]) }}'" >
                                                            Profile
                                                        </button>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        @if($user->banner !== 'nope')
                                            <img style="box-sizing: border-box; max-height: 20rem" src="{{ $user->banner }}" class="rounded mx-auto d-block" alt="banner">
                                        @endif
                                            <form method="POST" action="{{ route('usermenuedit') }}">
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

                                                <div>
                                                    <x-label for="npass" :value="__('New Password | Leave blank for no changes')" />

                                                    <x-input id="npass" class="block mt-1 w-full" type="password" name="npass" autofocus />
                                                </div>
                                                <div>
                                                    <x-label for="cpass" :value="__('Current Password')" />

                                                    <x-input id="cpass" class="block mt-1 w-full" type="password" name="cpass" required autofocus />
                                                </div>

                                                <div id="usermodify"></div>

                                                <x-button class="mt-2">
                                                    {{ __('Modify') }}
                                                </x-button>
                                            </form>
                                            {!!  GoogleReCaptchaV3::render(['usermodify'=>'usermodify']) !!}
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
