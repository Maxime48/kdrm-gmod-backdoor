@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payloads') }}
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


                        <div class="d-flex flex-wrap justify-content-between">
                            <!--<div class="col-12 col-md-3 p-0 mb-3"> <input type="text" class="form-control" placeholder="Search..."> </div>-->
                            <a href="{{ route('addNewPayload') }}"  class="btn btn-primary">New Payload</a>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header pl-0 pr-0">
                                <div class="row no-gutters w-110 align-items-center">
                                    <div class="col-12 text-muted">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-1">#</div>
                                            <div class="col-2">Description</div>
                                            <div class="col-2">Content</div>
                                            <div class="col-2">Created at</div>
                                            <div class="col-2">Updated at</div>
                                            <div class="col-3">Launch</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach($payloads as $payload)

                                <div class="card-body">
                                    <div class="row no-gutters">

                                        <div class="col-1">

                                            {{ $loop->index }}
                                            <a href="{{ route('editPayload', ['payloadid' => $payload->id]) }}"  class="btn btn-secondary">Edit</a>
                                            <a href="{{ route('deletePayload', ['payloadid' => $payload->id]) }}"  class="btn btn-danger">
                                                Delete
                                            </a>

                                        </div>

                                        <div class="col-2">

                                            {{ \Illuminate\Support\Str::limit($payload->description, 200, $end='...') }}

                                        </div>

                                        <div class="col-2">

                                            {{ \Illuminate\Support\Str::limit($payload->content, 200, $end='...') }}

                                        </div>

                                        <div class="col-2">

                                            {{ adminLogic::time_elapsed_string($payload->created_at) }}
                                            <br>
                                            {{ $payload->created_at }}

                                        </div>

                                        <div class="col-2">

                                            {{ adminLogic::time_elapsed_string($payload->updated_at) }}
                                            <br>
                                            {{ $payload->updated_at }}

                                        </div>

                                        <div class="col-2">

                                            <form method="POST" action="{{ route('sendPayload') }}">
                                                @csrf
                                                <div>
                                                    <x-input id="payloadid" class="block mt-1 w-full" type="number" name="payloadid" value="{{$payload->id}}" hidden readonly required  />
                                                </div>

                                                <div>
                                                    <select name="serverid" id="serverid">
                                                        <option value="">--Please choose an option--</option>
                                                        @foreach($servers as $server)
                                                            <option value="{{ $server->id }}">
                                                                {{ $loop->index. ' | ' .$server->name ?? 'noname'}}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <x-button class="mt-1">
                                                        {{ __('Send') }}
                                                    </x-button>

                                                </div>
                                            </form>

                                        </div>

                                    </div>
                                </div>
                        @endforeach

                        <!--
                        <nav>
                            <ul class="pagination mb-5">
                                <li class="page-item disabled"><a class="page-link" href="javascript:void(0)" data-abc="true">«</a></li>
                                <li class="page-item active"><a class="page-link" href="javascript:void(0)" data-abc="true">1</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)" data-abc="true">2</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)" data-abc="true">3</a></li>
                                <li class="page-item"><a class="page-link" href="javascript:void(0)" data-abc="true">»</a></li>
                            </ul>
                        </nav>
                            -->
                        </div>
                        <div class="btn-group mx-auto"  role="group">
                            @for($i = 1; $i <= $buttons; $i++)
                                @if($buttons > 30)
                                    @if($i<=8 or ($pageid+5 >= $i and $pageid-5 <= $i) or $i >= ($buttons-8))
                                        <button type="button" onclick="location.href='{{route('userPayloads', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                    @endif
                                @else
                                    <button type="button" onclick="location.href='{{route('userPayloads', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                @endif
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
