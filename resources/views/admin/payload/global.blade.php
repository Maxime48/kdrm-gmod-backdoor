@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Global Payloads') }}
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
                <div class="overflow-hidden">
                    <div class="p-6">

                    <div class="container-fluid mt-100">


                        <div class="d-flex flex-wrap justify-content-between">
                            <!--<div class="col-12 col-md-3 p-0 mb-3"> <input type="text" class="form-control" placeholder="Search..."> </div>-->
                            <a href="{{ route('CreateGlobalPayload') }}"  class="btn btn-primary mb-2">New Global Payload</a>
                        </div>

                        <div class="table100 ver1 m-b-110">
                            <div class="table100-head">
                                <table>
                                    <thead>
                                    <tr class="row100 head">
                                        <th style="text-align: center;" class="cell100 column2">Actions</th>
                                        <th class="cell100 column2">Description</th>
                                        <th class="cell100 column3">Content</th>
                                        <th class="cell100 column2">Created at</th>
                                        <th class="cell100 column5">Updated at</th>
                                        <th class="cell100 column5">Admin</th>
                                        <th class="cell100 column5">Downloads</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="table100-body js-pscroll">
                                <table>
                                    <tbody>
                                    @foreach($payloads as $payload)
                                        <tr class="row100 body">
                                            <td class="cell100 column2">
                                                <div style="text-align: center;">
                                                    <a href="{{ route('editGlobalPayload', ['payloadid' => $payload->id]) }}"  class="btn btn-secondary">Edit</a>
                                                    <a href="{{ route('deleteGlobalPayload', ['payloadid' => $payload->id]) }}"  class="btn btn-danger">
                                                        Delete
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="cell100 column2">
                                                {{ \Illuminate\Support\Str::limit($payload->description, 200, $end='...') }}
                                            </td>
                                            <td class="cell100 column3">
                                                {{ \Illuminate\Support\Str::limit($payload->content, 50, $end='...') }}
                                            </td>
                                            <td class="cell100 column2">
                                                {{ adminLogic::time_elapsed_string($payload->created_at) }}
                                                <br>
                                                {{ $payload->created_at }}
                                            </td>
                                            <td class="cell100 column5">
                                                {{ adminLogic::time_elapsed_string($payload->updated_at) }}
                                                <br>
                                                {{ $payload->updated_at }}
                                            </td>
                                            <td class="cell100 column5">
                                                {{ adminLogic::getUserById($payload->user_id)->name }}
                                            </td>
                                            <td class="cell100 column5">
                                                {{ $payload->copies }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div  class="btn-group mt-2"  role="group">
                                @for($i = 1; $i <= $buttons; $i++)
                                    @if($buttons > 30)
                                        @if($i<=8 or ($pageid+5 >= $i and $pageid-5 <= $i) or $i >= ($buttons-8))
                                            <button type="button" onclick="location.href='{{route('GlobalPayloads', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                        @endif
                                    @else
                                        <button type="button" onclick="location.href='{{route('GlobalPayloads', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                    @endif
                                @endfor
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
