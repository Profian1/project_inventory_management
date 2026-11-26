@php($supplier = $supplier ?? null)

@csrf

@if ($supplier)
    @method('PUT')
@endif

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $supplier?->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $supplier?->phone ?? '')" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $supplier?->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $supplier?->address ?? '')" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes', $supplier?->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>
