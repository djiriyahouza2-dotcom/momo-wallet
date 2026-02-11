<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rétrait d'argent
        </h2>
    </x-slot>

    @if (session('error'))
        <div class="bg-red-300 p-2">
            <span class="text-red-800">{{ session('error') }}</span>
        </div>
    @endif


    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('transactions.withdraw') }}" class="space-y-6">
                        @csrf
                        <!-- Amount Input -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Montant (balance:
                                {{ $balance }})</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="number" name="amount" id="amount" min="10"
                                    max="{{ $balance }}" required value="{{ old('amount') }}"
                                    class="flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300"
                                    placeholder="Montant a rétirer">
                                <span
                                    class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    {{ $user->currency ?? 'USD' }}
                                </span>
                            </div>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Operator Select -->
                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700">Opérateur</label>
                            <select id="provider" name="provider" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Sélectionner un opérateur</option>
                                @foreach ($payment_config['providers'] as $item)
                                    <option value="{{ $item['provider'] }}" @selected(old('provider') === $item['provider'])>
                                        {{ $item['displayName'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number Input -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Numéro de
                                téléphone</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    +{{ $payment_config['prefix'] }}
                                </span>
                                <input type="tel" name="phone" id="phone" required pattern="[0-9]{9}"
                                    maxlength="9" value="{{ old('phone') }}"
                                    class="flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300"
                                    placeholder="Entrez les 9 chiffres">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Entrez les 9 chiffres après +243</p>
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                                Rétrait
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
