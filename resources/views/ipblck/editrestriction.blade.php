<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit a restriction') }}
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

                    <form method="POST" action="{{ route('UserEditRestrictionPost', ['restriction' => $restriction->id]) }}">
                        @csrf
                        <div>
                            <!--<x-label for="content" :value="__('Restricted Ip')" />-->

                            <textarea id="ip"
                                      name="ip"
                                      class="block mt-1 w-full rounded"
                                      oninput="this.parentNode.dataset.value = this.value"
                                      rows="1"
                                      required autofocus>{{$restriction->forbiddenIp}}</textarea>

                        </div>

                        <div>
                            <x-input id="restrictionid" class="block mt-1 w-full" type="number" name="restrictionid" value="{{$restriction->id}}" hidden readonly required  />
                        </div>

                        {!!  GoogleReCaptchaV3::renderField('editrestriction', 'editrestriction') !!}

                        <x-button class="mt-2">
                            {{ __('Save') }}
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
