@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Backdoor') }}
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


                    <div class="container-fluid">
                        @if($backdoor != 'nope')
                            {{$backdoor}}
                        @else
                            <div class="alert alert-primary" role="alert">
                                No infection key was detected, please generate one <br>
                                Do not share this, this is unique. Ask an admin to regen a new one
                            </div>

                            <button type="button" onclick="document.location.href='{{ route('regenBackdoor') }}';" class="btn btn-danger">Regen Key</button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
