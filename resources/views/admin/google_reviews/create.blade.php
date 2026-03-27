@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header card-header-primary">
            <h1 class="card-title"><b>Add Homepage Review</b></h1>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.google-reviews.store') }}" method="POST">
                @include('admin.google_reviews._form', ['buttonText' => 'Save Review'])
            </form>
        </div>
    </div>
</div>
@endsection
