@extends('frontend.layouts.app', ['title' => 'Return & Exchange'])


@section('content')
   
    <div class="container common-class-page">
        <div class="row">
            {!! $return_and_exchange[0]->description !!}
        </div>
    </div>
@endsection
