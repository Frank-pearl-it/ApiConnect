<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Company;

class AutoTaskWebhookController extends Controller
{


public function handleCompanyCreated(Request $request)
{
    $data = $request->validate([
        'CompanyID' => 'required',
        'CompanyName' => 'required|string',
        'PrimaryEmail' => 'nullable|email',
        'DomainName' => 'nullable|string',
        'UsesMicrosoftLogin' => 'nullable|boolean',
    ]);

    $company = Company::updateOrCreate(
        ['autotask_id' => $data['CompanyID']],
        [
            'name' => $data['CompanyName'],
            'domain' => $data['DomainName'] ?? null,
            'primary_email' => $data['PrimaryEmail'] ?? null,
            'uses_microsoft_login' => $data['UsesMicrosoftLogin'] ?? false,
        ]
    );

    if ($company->users()->count() === 0) {
        app(\App\Services\UserService::class)->createFirstUserForCompany($company);
    }

    return response()->json(['status' => 'ok']);
}

} 