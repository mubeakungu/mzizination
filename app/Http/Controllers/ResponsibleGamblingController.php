<?php

namespace App\Http\Controllers;

use App\Services\ResponsibleGambling;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResponsibleGamblingController extends Controller
{
    public function init()
    {
        return ResponsibleGambling::summary($this->user);
    }

    public function submitKyc(Request $request)
    {
        if (in_array($this->user->kyc_status, ['pending', 'verified'])) {
            return ['error' => true, 'message' => 'Your details are already submitted. Contact support to change them.'];
        }

        $validator = Validator::make($request->all(), [
            'date_of_birth' => 'required|date|before:-' . ResponsibleGambling::MIN_AGE . ' years',
            'id_number' => 'required|string|regex:/^[A-Za-z0-9]{6,20}$/',
        ], [
            'date_of_birth.before' => 'You must be ' . ResponsibleGambling::MIN_AGE . ' or older to play.',
            'id_number.regex' => 'Enter a valid national ID or passport number.',
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        $taken = User::where('id_number', $request->id_number)->where('id', '!=', $this->user->id)->exists();
        if ($taken) {
            return ['error' => true, 'message' => 'This ID number is already linked to another account.'];
        }

        $this->user->update([
            'date_of_birth' => $request->date_of_birth,
            'id_number' => strtoupper($request->id_number),
            'kyc_status' => 'pending',
            'kyc_submitted_at' => now(),
        ]);

        return ResponsibleGambling::summary($this->user);
    }

    public function setLimits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deposit' => 'nullable|numeric|min:0|max:100000000',
            'stake' => 'nullable|numeric|min:0|max:100000000',
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        ResponsibleGambling::setLimits($this->user, $request->only(['deposit', 'stake']));

        return ResponsibleGambling::summary($this->user);
    }

    public function selfExclude(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => ['required', Rule::in(array_map('strval', ResponsibleGambling::EXCLUSION_DAYS))],
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => 'Choose a valid period'];
        }

        ResponsibleGambling::selfExclude($this->user, $request->period);

        return ResponsibleGambling::summary($this->user);
    }
}
