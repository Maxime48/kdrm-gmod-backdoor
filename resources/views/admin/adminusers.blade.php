<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('userspage', ['id' => "finder"]) }}">
                <div class="mb-2">
                    <x-input id="search" class="block mt-1 w-full" name="search" :value="old('maxime')" required autofocus />

                    <x-button class="ml-3">
                        {{ __('Search') }}
                    </x-button>

                </div>
            </form>
            <div class="container d-flex justify-content-center">
                <div class="row row-cols-1 @if(count($users)<2) row-cols-md-1 @else row-cols-md-2 @endif g-3">
                    @foreach($users as $user)
                        <div class="col">
                            <div class="box box-widget widget-user mx-2">
                                <div @if($user->banner !== 'nope')
                                     style="background-image: url({{$user->banner}}) !important; background-size: 100%;"
                                     @endif
                                     class="widget-user-header
                             @if($user->banner == 'nope')
                                         bg-aqua-active
                             @endif">
                                    <h3 class="widget-user-username">{{$user->name}}</h3>
                                    <h5 class="widget-user-desc">{{$user->bio}}</h5>
                                </div>
                                <a class="d-flex widget-user-image" href="{{ route('user', ['id' => $user->id]) }}">
                                    <img class="img-circle"
                                         @if($user->avatar == 'nope')
                                         src="https://img.icons8.com/color/36/000000/administrator-male.png"
                                         @else
                                         src="{{$user->avatar}}"
                                         @endif
                                         style="width: 6rem; height: 6rem;"
                                         alt="Avatar">
                                </a>
                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-sm-6 border-right">
                                            <div class="description-block">
                                                <h5 class="description-header">{{$user->email}}</h5>
                                                <span class="description-text">
                                                    @if($user->admin == 1)
                                                        Moderator
                                                    @elseif($user->admin == 2)
                                                        Admin
                                                    @elseif($user->admin == -1)
                                                        Banned Lmao
                                                    @else
                                                        User
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 border-right">
                                            <div class="description-block">
                                                <h5 class="description-header">{{$user->created_at}}</h5>
                                                <span class="description-text">{{$user->updated_at}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            </div>
            <div class="btn-group mx-auto"  role="group">
                @for($i = 1; $i <= $buttons; $i++)
                    @if($buttons > 30)
                        @if($i<=8 or ($pageid+5 >= $i and $pageid-5 <= $i) or $i >= ($buttons-8))
                            <button type="button" onclick="location.href='{{route('userspage', ['id' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                        @endif
                    @else
                        <button type="button" onclick="location.href='{{route('userspage', ['id' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                    @endif
                @endfor
            </div>
        </div>
    </div>
</x-app-layout>
