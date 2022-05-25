@php
    use \App\Http\Controllers\kermini\adminLogic as adminLogic;
    use \App\Http\Controllers\kermini\userLogic as userLogic;
    use Illuminate\Support\Str as Str;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Images') }}
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
                <div class="p-6 bg-white border-b border-gray-200 mx-auto">

                    <div style="text-align: center;">
                        <div class="btn-group" role="group">
                            @for($i = 1; $i <= $buttons; $i++)
                                @if($buttons > 30)
                                    @if($i<=8 or ($pageid+5 >= $i and $pageid-5 <= $i) or $i >= ($buttons-8))
                                        <button type="button" onclick="location.href='{{route('showImages', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                    @endif
                                @else
                                    <button type="button" onclick="location.href='{{route('showImages', ['pageid' => $i])}}';" class="btn btn-dark">{{ $i }}</button>
                                @endif
                            @endfor
                        </div>
                    </div>

                    <div class="container-fluid mt-100">
                        <div class="row">
                            @if(count($images) == 0)
                                <div style="text-align: center;" class="mb-3">
                                    No image found
                                </div>
                            @endif
                            @foreach($images as $image)
                                @if( Str::afterLast($image->fileName, ".") == "mp4")
                                    <div class="card mt-2 mr-1" style="width: 18rem;">
                                        <video style="max-height: 10rem;" class="card-img-top rounded mx-auto d-block" controls>
                                            <source src="{{ request()->getBasePath() . "/" . $image->referencePath }}"
                                                    style="max-height: 10rem;"
                                                    type="video/mp4">
                                        </video>
                                        <div class="card-body mx-auto">
                                            <a href="{{route('deleteImage', ['imageid' => $image->id])}}" class="btn btn-danger">Delete</a>
                                        </div>
                                        <div class="mx-auto">
                                            {{ $image->fileName }}
                                        </div>
                                        <div class="mx-auto">
                                            Size: {{ $image->fileSize }}
                                        </div>
                                    </div>
                                @else
                                    <div class="card mt-2 mr-1" style="width: 18rem;">
                                        <img class="card-img-top rounded mx-auto d-block"
                                             style="max-height: 10rem;"
                                             src="{{ request()->getBasePath() . "/" . $image->referencePath }}">
                                        <div class="card-body mx-auto">
                                            <a href="{{route('deleteImage', ['imageid' => $image->id])}}" class="btn btn-danger">Delete</a>
                                        </div>
                                        <div class="mx-auto">
                                            {{ $image->fileName }}
                                        </div>
                                        <div class="mx-auto">
                                            Size: {{ $image->fileSize }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <form method="POST" action="{{ route('postImage') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Image Upload -->
                            <div class="row justify-content-center mt-4 w-full">

                                <x-input id="image" class="block col-md-4" type="file" name="image" required autofocus />

                                <div id="postImage"></div>

                                <x-button class="col-lg-1">
                                    {{ __('Upload') }}
                                </x-button>
                            </div>
                        </form>
                        {!!  GoogleReCaptchaV3::render(['postImage'=>'postImage']) !!}

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
