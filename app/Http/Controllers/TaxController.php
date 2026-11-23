<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class TaxController extends Controller
{
    private function authorizeTaxAccess(Tax $tax): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if ($user->hasRole('admin')) {
            return;
        }
        if ($user->hasRole('organization') && (int) $tax->user_id === (int) $user->id) {
            return;
        }
        abort(403);
    }

    public function index()
    {
        abort_if(Gate::denies('tax_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if (Auth::user()->hasRole('admin')) {
            $taxes = Tax::orderBy('id', 'DESC')->get();
        } elseif (Auth::user()->hasRole('organization')) {
            $taxes = Tax::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
        } else {
            abort(403);
        }
        return view('admin.tax.index', compact('taxes'));
    }

    public function create()
    {
        abort_if(Gate::denies('tax_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.tax.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('tax_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'name' => 'bail|required',
            'price' => 'bail|required',
        ]);
        $data = $request->all();
        if (!Auth::user()->hasRole('admin')) {
            $data['user_id'] = Auth::user()->id;
        }
        if (!isset($request->allow_all_bill)) {
            $data['allow_all_bill'] = 0;
        }
        Tax::create($data);
        return redirect()->route('tax.index')->withStatus(__('Tax is added successfully.'));
    }

    public function show(Tax $tax)
    {
    }

    public function edit(Tax $tax)
    {
        abort_if(Gate::denies('tax_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeTaxAccess($tax);
        return view('admin.tax.edit', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        abort_if(Gate::denies('tax_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->authorizeTaxAccess($tax);

        $request->validate([
            'name' => 'bail|required',
            'price' => 'bail|required',
        ]);
        $data = $request->all();
        if (!isset($request->allow_all_bill)) {
            $data['allow_all_bill'] = 0;
        }
        if (!Auth::user()->hasRole('admin')) {
            $data['user_id'] = Auth::user()->id;
        }
        $tax->update($data);
        return redirect()->route('tax.index')->withStatus(__('Tax is updated successfully.'));
    }

    public function destroy(Tax $tax)
    {
    }
}
