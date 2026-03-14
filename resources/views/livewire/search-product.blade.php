<div class="header-search">
    <a href="#" class="search-toggle" role="button"><i class="icon-search"></i></a>
    <form wire:submit="submit"  class="form-search" wire:ignore.self>
        <div class="header-search-wrapper" wire:ignore.self>
            <label for="q" class="sr-only">Search</label>
            <input onclick="showDiv()" type="search" wire:model.live="searchText" value="{{ $searchText }}"
                wire:keydown="searchProduct" type="search" class="form-control" name="q" id="searchText"
                placeholder="Enter your keywords..." required="">
            {{-- @dd($searchText , preg_match('/^[\w\s]+$/', $searchText)) --}}
            @if (count($products) > 0)


                <ul id="suggestions" class="">
                    @forelse ($products as $product)
                        {{-- @dd($product) --}}
                        <li class="product-list-s">
                            <a href="{{ route('single.product.view', $product['slug']) }}" class="">
                                <img src="{{ asset('storage/' . $product['product_image']) }}"
                                    alt="{{ $product['product_name'] }}">
                                <p>{{ $product['product_name'] }}</p>
                            </a>
                        </li>

                    @empty

                        @if ($searchText)
                            <li>
                                <a class="text-black text-decoration-none d-flex align-items-center w-100">
                                    <p>No Products Found </p>
                                </a>
                            </li>
                        @endif
                    @endforelse



                </ul>
            @endif
        </div><!-- End .header-search-wrapper -->


    </form>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $(".search-toggle").on('click', function() {
                if ($('#searchText').val() == "") {
                    $("#suggestions").remove();
                }

                if ($('.product-list-s').length == 0) {
                    $("#suggestions").remove();
                }
            });
        });

        function showDiv() {
            const div = document.getElementById("suggestions");
            div.style.display = "block";


            // Add event listener to the document
            document.addEventListener("click", function handler(e) {
                if (!div.contains(e.target) && e.target !== document.querySelector("button")) {
                    div.style.display = "none";
                    $("#suggestions").html(" ")

                    if ($('#searchText').val() == "") {

                        $("#suggestions").remove();
                    }
                    // Set the property directly
                    @this.set('searchText', '');

                    // Remove listener after hiding
                    document.removeEventListener("click", handler);
                }
            });
        }
    </script>
</div><!-- End .header-search -->
