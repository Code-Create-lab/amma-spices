@extends('frontend.layouts.app', ['title' => '#'])

@section('content')
    {{-- @dd($slug) --}}
    {{-- @livewire('shop-page-filter') --}}
    {{-- @dd($on_sale) --}}
    <livewire:shop-page-filter :slug="$slug ?? ''" />
@endsection
