<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Supplier</h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @include('suppliers._form', ['supplier' => $supplier])

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Update Supplier</x-primary-button>
                <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
