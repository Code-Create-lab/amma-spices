@extends('admin.layout.app', ['title' => 'Attributes Edit'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                @if (session()->has('success'))
                    <div class="alert alert-success">
                        @if (is_array(session()->get('success')))
                            <ul>
                                @foreach (session()->get('success') as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        @else
                            {{ session()->get('success') }}
                        @endif
                    </div>
                @endif
                @if (count($errors) > 0)
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first() }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                @endif
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">{{ __('keywords.Edit') }} {{ __('keywords.Attribute') }}</h4>
                    </div>
                    <div class="card-body">
                        <form class="forms-sample" action="{{ route('attributes.update', $attribute->uuid) }}"
                            method="post" enctype="multipart/form-data" id="attribute-update-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Name') }}</label>
                                        <input type="text" name="name" class="form-control" readonly
                                            value="{{ $attribute->name }}" required  />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="bmd-label-floating">{{ __('keywords.Options') }}</label>
                                        <input type="hidden" name="options[]" id="attribute-options-hidden">
                                        <input type="text" id="attribute-options"
                                            class="text-[#8e8e8e] font-['Roboto'] text-sm font-normal block w-full py-[1px] px-[14px] bg-white border border-solid border-[#d9dee3] rounded-[6px] bg-clip-padding mt-2"
                                            placeholder="Enter Values" aria-describedby="attributes" autofocus />
                                    </div>
                                </div>
                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary pull-center">{{ __('keywords.Submit') }}</button>
                            <a href="{{ route('attributes') }}" class="btn">{{ __('keywords.Close') }}</a>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.33.2/tagify.min.js"></script>
        <script>
            var input = document.querySelector("#attribute-options");
            var existingOptions = {!! json_encode($attribute->values->pluck('name')->toArray()) !!};
            var tagify = new Tagify(input, {
                whitelist: existingOptions,
                enforceWhitelist: false,
                dropdown: {
                    enabled: 0
                }
            });
            tagify.addTags(existingOptions); // Pre-fill with existing options
            $('#attribute-update-form').submit(function() {
                var options = tagify.value.map(tag => tag.value); // Extract tag values
                $('#attribute-options-hidden').val(JSON.stringify(options)); // Set hidden input value
            });
        </script>
        {!! JsValidator::formRequest(
            'App\Http\Requests\Admin\Attribute\AttributeUpdateRequest',
            '#attribute-update-form',
        ) !!}
    @endpush
@endsection
