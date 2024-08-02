<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Ramsey\Uuid\Uuid;
use App\Mail\GiftMail;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\SubsCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TransactionsController extends Controller
{
    private function generateTransactionToken()
    {
        do {
            $token = Str::random(32);
        } while (Transaction::where('token', $token)->exists());

        return $token;
    }

    private function generateUniqueCode()
    {
        $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        return $code;
    }

    public function paymentDetail(SubsCategory $product, Request $request)
    {
        $gift = $request->has('gift') ? 'true' : 'false';
        $redeemed = $request->has('redeem_code') ? $request->redeem_code : 'false';

        return view('payment_detail', ["product" => $product, "gift" => $gift, "redeemed" => $redeemed]);
    }

    public function processPayment(Request $request)
    {

        $data = $request->json()->all();

        $subsCategory = SubsCategory::join('transactions', 'transactions.subs_category_id', '=', 'subs_categories.id')
            ->select('subs_categories.*')
            ->where('subs_categories.id', '=', $request->input('subsId'))
            ->first();

        $transaction = Transaction::create([
            'user_id' => Auth::user()->id,
            'token' => '',
            'subs_category_id' => $data["subs_id"],
            'discounted_price' => $data["discounted_price"],
            'status' => 'pending',
        ]);

        if ($data["isRedeemed"] == "false") {
            // Set your Merchant Server Key
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            // Set to Development/Sandbox Environment (default). Set to true fmidtrans.serverKeyor Production Environment (accept real transaction).
            \Midtrans\Config::$isProduction = false;
            // Set sanitization on (default)midtrans.serverKey
            \Midtrans\Config::$isSanitized = true;
            // Set 3DS transaction for credit card to truemidtrans.serverKey
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => rand(),
                    'gross_amount' => ceil($data['discounted_price']),
                ],
                'items_details' => [
                    'id' => $transaction->subs_category_id,
                    'price' => $transaction->discounted_price,
                    'quantity' => 1,
                    "name" => Str::headline($transaction->subsCategory->slug),
                ],
                'customer_details' => [
                    'email' => Auth::user()->email,
                    'first_name' => Auth::user()->first_name,
                    'last_name' => Auth::user()->last_name,
                    'phone' => Auth::user()->phone_number,
                    'shipping_address' => [
                        'address' => Auth::user()->address->street_number . ', ' . Auth::user()->address->district . ', ' . Auth::user()->address->village . ', ' . Auth::user()->address->city . ', ' . Auth::user()->address->country,
                        'postal_code' => Auth::user()->address->postal_code
                    ]
                ]
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $transaction->snap_token = $snapToken;
        } else {
            $transaction->status = "success";
        }

        $transaction->token = $this->generateTransactionToken();
        $transaction->save();

        return json_encode($transaction);
    }

    public function paymentSuccess($token, $isGift, $isRedeemed)
    {
        $transactionUpdate = Transaction::where('transactions.token', '=', $token)->first();

        if ($isGift != "false") {
            $giftCode = $this->generateUniqueCode();
            Gift::create([
                'transaction_id' => $transactionUpdate->id,
                'redeem_code' => $giftCode,
                'is_redeemed' => false,
            ]);

            Mail::to(Auth::user()->email)->send(
                new GiftMail($giftCode)
            );
        }

        if ($isRedeemed != "false") {
            $giftUpdate = Gift::where('redeem_code', '=', $isRedeemed)->first();

            $giftUpdate->is_redeemed = true;
            $giftUpdate->save();
        }

        $transactionUpdate->status = "success";
        $transactionUpdate->save();

        return redirect()->route('index');
    }

    public function paymentFailed($token)
    {

        $transactionUpdate = Transaction::where('transactions.token', '=', $token)->first();
        $transactionUpdate->status = "failed";
        $transactionUpdate->save();

        return redirect()->route('index');
    }
}
