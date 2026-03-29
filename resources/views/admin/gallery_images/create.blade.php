@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-header card-header-primary">
            <h1 class="card-title"><b>Add Gallery Image</b></h1>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.gallery-images.store') }}" method="POST" enctype="multipart/form-data">
                @include('admin.gallery_images._form', ['buttonText' => 'Save Image', 'imageRequired' => true])
            </form>
        </div>
    </div>
</div>
@endsection
