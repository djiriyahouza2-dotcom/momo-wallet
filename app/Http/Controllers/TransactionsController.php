<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TransactionsController extends Controller
{
    public function getDepositForm()
    {
        $user = User::find(Auth::id());
        $providers_data = $this->getProvidersAvailable($user->country);

        if (empty($providers_data) || empty($providers_data['countries'][0]['providers'])) {
            return redirect()->back()->with('error', "Aucun fournisseur de paiement disponible pour votre pays.");
        }

        return view('transactions.deposit-form', [
            'payment_config' => $providers_data['countries'][0]
        ]);
    }

    public function getWithdrawForm()
    {
        $user = User::find(Auth::id());

        if ($user->balance < 10) {
            return redirect()->back()->with('error', 'Votre solde est insuffisant pour effectuer un retrait. Le montant minimum de retrait est de 1000');
        }

        $providers_data = $this->getProvidersAvailable($user->country, 'PAYOUT');

        if (empty($providers_data) || empty($providers_data['countries'][0]['providers'])) {
            return redirect()->back()->with('error', "Aucun fournisseur de transfert d'argent disponible pour votre pays.");
        }

        return view('transactions.withdraw-form', [
            'payment_config' => $providers_data['countries'][0],
            'balance' => $user->balance
        ]);
    }

    public function initDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|min:1',
            'phone' => 'required|min:9',
            'provider' => 'required'
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        if (!$transaction) {
            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayez !");
        }

        try {
            $phone =  preg_replace('/\D/', '', '243' . $request->phone);
            $endpoint = config('services.pawapay.api_url') . '/deposits';

            $payload = [
                "depositId" => $transaction->id,
                "amount" => $transaction->amount,
                "currency" => Auth::user()->currency,
                "payer" => [
                    "type" => "MMO",
                    "accountDetails" => [
                        "phoneNumber" => $phone,
                        "provider" => $request->provider
                    ]
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.pawapay.api_key'),
                'Content-Tyoe' => 'application/json'
            ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'ACCEPTED') {
                    return redirect()->route('transactions.status', $transaction->id);
                } else {
                    $transaction->status = 'failed';
                    $transaction->note = $data['failureReason']['failureMessage'];
                    $transaction->save();
                    return redirect()->route('transactions.status', $transaction->id);
                }
            } else {
                // 
                return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayez !");
            }
        } catch (\Throwable $th) {
            //throw $th;
            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayez !");
        }
    }

    public function initWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|min:2',
            'phone' => 'required|min:9',
            'provider' => 'required'
        ]);

        $transaction = Transaction::create([
            'type' => 'withdraw',
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        if (!$transaction) {
            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayez !");
        }

        try {
            $phone =  preg_replace('/\D/', '', '243' . $request->phone);
            $endpoint = config('services.pawapay.api_url') . '/payouts';

            $payload = [
                "payoutId" => $transaction->id,
                "amount" => $transaction->amount,
                "currency" => Auth::user()->currency,
                "recipient" => [
                    "type" => "MMO",
                    "accountDetails" => [
                        "phoneNumber" => $phone,
                        "provider" => $request->provider
                    ]
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.pawapay.api_key'),
                'Content-Tyoe' => 'application/json'
            ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'ACCEPTED') {
                    return redirect()->route('transactions.status', $transaction->id);
                } else {
                    $transaction->status = 'failed';
                    $transaction->note = $data['failureReason']['failureMessage'];
                    $transaction->save();
                    return redirect()->route('transactions.status', $transaction->id);
                }
            } else {
                // 
                return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayez !");
            }
        } catch (\Throwable $th) {
            //throw $th;
            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayez !");
        }
    }

    public function getTransactionStatus($transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);
        $user = User::find(Auth::id());

        $data = $this->verifyTransactionStatus($transaction_id, $transaction->type);

        if (!$data) {
            return view('transactions.status', compact('transaction'));
        }

        if ($data['status'] === 'COMPLETED') {
            $transaction->status = 'completed';

            if ($transaction->type === 'deposit') {
                $user->updateBalance($transaction->amount);
            } elseif ($transaction->type === 'withdraw') {
                $user->updateBalance(-1 * $transaction->amount);
            }
        } elseif ($data['status'] === 'FAILED') {
            $transaction->status = 'failed';
            $transaction->note = $data['failureReason']['failureMessage'];
        } else {
            $transaction->status = 'pending';
        }

        $transaction->save();
        $transaction->refresh();

        return view('transactions.status', compact('transaction'));
    }

    protected function getProvidersAvailable($country, $type = 'DEPOSIT')
    {
        try {

            $endpoint = config('services.pawapay.api_url') . '/active-conf';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.pawapay.api_key'),
                'Content-Tyoe' => 'application/json'
            ])
                ->withQueryParameters([
                    'country' => $country,
                    'operationType' => $type
                ])
                ->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();

                return $data;
            } else {
                dd($response->body());
                return [];
            }
        } catch (\Throwable $th) {
            return [];
        }
    }

    protected function verifyTransactionStatus($transaction_id, $type = 'deposit')
    {
        try {
            if ($type === 'withdraw') {
                $endpoint = config('services.pawapay.api_url') . '/payouts/' . $transaction_id;
            } else {
                $endpoint = config('services.pawapay.api_url') . '/deposits/' . $transaction_id;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.pawapay.api_key'),
                'Content-Tyoe' => 'application/json'
            ])
                ->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'FOUND') {
                    return $data['data'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            return null;
        }
    }
}
