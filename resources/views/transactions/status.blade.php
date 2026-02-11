<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Statut de la transaction
        </h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($transaction->status === 'pending')
                        <div class="text-center">
                            <svg class="mx-auto mb-4 h-12 w-12 text-yellow-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01" />
                            </svg>
                            @if ($transactions->type == 'deposit')
                                <h3 class="text-lg font-semibold mb-2">Paiement en attente</h3>
                                <p class="mb-4">Veuillez valider la transaction sur votre téléphone. Une fois terminé,
                                    cliquez sur le bouton ci-dessous pour vérifier votre paiement.</p>
                                <a href="{{ route('transactions.status', $transaction->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                                    Vérifier le paiement
                                </a>
                            @else
                                <h3 class="text-lg font-semibold mb-2">Transfert en attente</h3>
                                <p class="mb-4">Votre retrait est en cours de traitement, vous recevrez une
                                    notification une fois le transfert terminé. Vous pouvez actualiser cette page pour
                                    vérifier le statut de votre retrait.</p>
                                <a href="{{ route('transactions.status', $transaction->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                                    Vérifier le paiement
                                </a>
                            @endif
                        </div>
                    @elseif($transaction->status === 'failed')
                        <div class="text-center">
                            <svg class="mx-auto mb-4 h-12 w-12 text-red-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 9l-6 6m0-6l6 6" />
                            </svg>
                            <h3 class="text-lg font-semibold mb-2 text-red-600">Échec du transaction</h3>
                            <p class="mb-2">Une erreur est survenue lors de votre transaction.</p>
                            <p class="mb-4 text-sm text-gray-500">Cause : {{ $transaction->note }}</p>

                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                                Réessayer un autre transaction
                            </a>
                        </div>
                    @elseif($transaction->status === 'completed')
                        <div class="text-center">
                            <svg class="mx-auto mb-4 h-12 w-12 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4" />
                            </svg>
                            @if ($transaction->type == 'deposit')
                                <h3 class="text-lg font-semibold mb-2 text-green-600">Paiement réussi</h3>
                                <p class="mb-4">Votre paiement a été validé avec succès et votre portefeuille a été
                                    crédité.</p>
                            @else
                                <h3 class="text-lg font-semibold mb-2 text-green-600">Transfert réussi</h3>
                                <p class="mb-4">Votre retrait a été traité avec succès et l'argent a été transféré
                                    vers votre compte mobile money.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
