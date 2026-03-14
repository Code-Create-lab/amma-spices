@extends('frontend.layouts.app', ['title' => 'Cancellation And Refund Policy'])

@section('content')
    <x-breadcrumb :items="[
        [
            'label' => 'Cancellation And Refund Policy',
            'url' => null,
        ],
    ]" />
    <div class="container common-class-page">
        <div class="row">
            <h1>{{ $cancel_and_refund[0]->title }}</h1>
            {!! $cancel_and_refund[0]->description !!}
        </div>
    </div>
@endsection
