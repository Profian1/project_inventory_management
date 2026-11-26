<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Product</h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @include('products._form', ['product' => $product])

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Update Product</x-primary-button>
                <a href="{{ route('products.show', $product) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
